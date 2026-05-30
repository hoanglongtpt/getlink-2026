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

        $tempPath = storage_path('app/temp/' . uniqid('resource_', true));
        @mkdir(dirname($tempPath), 0755, true);

        try {
            $response = Http::withOptions(['sink' => $tempPath])->get($history->direct_download_link);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to download direct file: ' . $response->status() . ' body=' . substr($response->body(), 0, 500));
            }

            $fileId = $driveService->uploadFile($tempPath, $history->original_link);
            $driveLink = $driveService->getViewerLink($fileId);

            $resource = Resource::firstOrCreate([
                'original_link' => $history->original_link,
            ], [
                'provider' => $history->provider,
                'is_premium' => false,
                'file_name' => basename($tempPath),
                'google_drive_link' => $driveLink,
                'google_drive_file_id' => $fileId,
                'status' => 'cached',
            ]);

            $history->update([
                'resource_id' => $resource->id,
                'status' => 'completed',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to process downloaded resource', [
                'history_id' => $this->downloadHistory->id,
                'error' => $exception->getMessage(),
            ]);
            $history->update(['status' => 'failed']);
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
