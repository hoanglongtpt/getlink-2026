<?php

namespace App\Jobs;

use App\Models\DownloadHistory;
use App\Models\Resource;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessDownloadedResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DownloadHistory $downloadHistory;

    public function __construct(DownloadHistory $downloadHistory)
    {
        $this->downloadHistory = $downloadHistory;
    }

    public function handle(GoogleDriveService $driveService): void
    {
        $history = $this->downloadHistory->fresh();

        if (! $history || $history->direct_download_link === null) {
            Log::warning('ProcessDownloadedResource called without direct download link', [
                'history_id' => $this->downloadHistory->id,
            ]);
            return;
        }

        $slug = pathinfo(parse_url($history->original_link, PHP_URL_PATH) ?: '', PATHINFO_BASENAME);
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', $slug) ?: 'resource';
        $tempPath = storage_path('app/temp/' . $slug . '_' . uniqid('', true));
        @mkdir(dirname($tempPath), 0755, true);

        try {
            // Tăng timeout lên 600 giây (10 phút) để xử lý file lớn
            $response = Http::timeout(600)->withOptions(['sink' => $tempPath])->get($history->direct_download_link);
            $successful = $response->successful();
            $statusCode = $response->status();
            $bodyPreview = substr($response->body(), 0, 500);

            // Try to close underlying PSR-7 stream to release file handle on Windows.
            if (method_exists($response, 'toPsrResponse')) {
                try {
                    $psr = $response->toPsrResponse();
                    if ($psr && method_exists($psr, 'getBody')) {
                        $body = $psr->getBody();
                        if (is_object($body) && method_exists($body, 'close')) {
                            try {
                                $body->close();
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            unset($response);
            // Force garbage collection to ensure any lingering stream resources are freed.
            if (function_exists('gc_collect_cycles')) {
                @gc_collect_cycles();
            }

            if (! $successful) {
                throw new \RuntimeException('Failed to download direct file: ' . $statusCode . ' body=' . $bodyPreview);
            }

                        $fileId = $driveService->uploadFile($tempPath, $history->original_link);
            
            // Lấy link nhưng không share public
            $driveLinkData = $driveService->getViewerLink($fileId);
            $driveLink = $driveLinkData['link'];

                            $resource = Resource::updateOrCreate([
                                'original_link' => $history->original_link,
                            ], [
                                // Save provider as provType (getstock_type) to allow precise cost lookup
                                'provider' => $history->getstock_type,
                'is_premium' => false,
                'file_name' => basename($tempPath),
                'google_drive_link' => $driveLink,
                'google_drive_file_id' => $fileId,
                'status' => 'cached',
            ]);

            // Share file cho user cụ thể
            $userEmail = $history->user->email;
            $shareData = $driveService->getViewerLink($fileId, $userEmail);
            
            $history->update([
                'resource_id' => $resource->id,
                'status' => 'completed',
                'direct_download_link' => $driveLink,
                'drive_permission_id' => $shareData['permission_id']
            ]);

            // Nếu share thành công, lên lịch thu hồi quyền sau 30 phút
            if ($shareData['permission_id']) {
                RevokeGoogleDrivePermission::dispatch($fileId, $shareData['permission_id'])
                    ->delay(now()->addMinutes(30));
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to process downloaded resource', [
                'history_id' => $this->downloadHistory->id,
                'error' => $exception->getMessage(),
            ]);
            $history->update(['status' => 'failed']);
        } finally {
            // Try to free any remaining resources and clear file stat cache before deletion.
            if (function_exists('gc_collect_cycles')) {
                @gc_collect_cycles();
            }
            if (function_exists('clearstatcache')) {
                @clearstatcache(true, $tempPath);
            }

            if (file_exists($tempPath)) {
                if (! @unlink($tempPath)) {
                    Log::warning('Failed to delete temp downloaded file', [
                        'history_id' => $this->downloadHistory->id,
                        'path' => $tempPath,
                    ]);
                }
            }
        }
    }
}
