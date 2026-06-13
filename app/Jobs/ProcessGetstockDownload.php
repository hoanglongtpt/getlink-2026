<?php

namespace App\Jobs;

use App\Models\DownloadHistory;
use App\Jobs\ProcessDownloadedResource;
use App\Services\GetstockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGetstockDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DownloadHistory $history;

    public function __construct(DownloadHistory $history)
    {
        $this->history = $history;
    }

    public function handle(GetstockService $service): void
    {
        $history = $this->history->fresh();

        if (! $history || ! $history->getstock_slug || ! $history->getstock_item_id || ! $history->getstock_type) {
            Log::warning('ProcessGetstockDownload missing getstock metadata', ['history_id' => $this->history->id]);
            return;
        }

        $attempts = 0;
        $itemDCode = null;

                while ($attempts < 30 && $itemDCode === null) {
            sleep(4);
            $attempts++;

            try {
                $statusResult = $service->checkDownloadStatus(
                    $history->getstock_slug,
                    $history->getstock_item_id,
                    (int) $history->is_premium,
                    $history->getstock_type
                );
                
                // Ghi log để kiểm tra tiến trình trả về từ API
                Log::info('Getstock Polling Status', [
                    'history_id' => $history->id,
                    'attempt' => $attempts,
                    'response' => $statusResult
                ]);

                                // Nếu status trả về là thất bại từ bên getstock (thường là trả về mảng kết quả 400 hoặc status != 1)
                if (isset($statusResult['status']) && $statusResult['status'] === 400) {
                    Log::warning('Getstock returned 400 failed status', ['history_id' => $history->id, 'response' => $statusResult]);
                    break;
                }
                
                if (isset($statusResult['result']) && is_array($statusResult['result'])) {
                    if (data_get($statusResult, 'result.status') === 1 && data_get($statusResult, 'result.itemDCode')) {
                        $itemDCode = data_get($statusResult, 'result.itemDCode');
                        break;
                    }
                    
                    if (data_get($statusResult, 'result.status') === 2 || data_get($statusResult, 'result.status') === -1) {
                        Log::warning('Getstock returned failed status code', ['history_id' => $history->id, 'response' => $statusResult]);
                        break;
                    }
                }
            } catch (\Throwable $exception) {
                Log::warning('Getstock status check failed', ['history_id' => $history->id, 'error' => $exception->getMessage()]);
            }
        }

        if (! $itemDCode) {
            $history->update(['status' => 'failed']);
            // Hoàn tiền đúng ví đã bị trừ khi Getstock không trả về file.
            $history->user->refundXu((int) $history->xu_cost, $history->xu_source ?? 'balance');
            return;
        }

        $directLink = $service->buildDirectDownloadLink($itemDCode);

                $history->update([
            'direct_download_link' => $directLink,
            'item_d_code' => $itemDCode,
            'status' => 'ready', // Tạm để ready, chờ Drive xử lý xong mới là completed
        ]);

        ProcessDownloadedResource::dispatch($history);
    }
}
