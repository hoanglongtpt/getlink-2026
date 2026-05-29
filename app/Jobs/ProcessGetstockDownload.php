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

        while ($attempts < 12 && $itemDCode === null) {
            sleep(5);
            $attempts++;

            try {
                $statusResult = $service->checkDownloadStatus(
                    $history->getstock_slug,
                    $history->getstock_item_id,
                    (int) $history->is_premium,
                    $history->getstock_type
                );

                if (data_get($statusResult, 'result.status') === 1 && data_get($statusResult, 'result.itemDCode')) {
                    $itemDCode = data_get($statusResult, 'result.itemDCode');
                    break;
                }
            } catch (\Throwable $exception) {
                Log::warning('Getstock status check failed', ['history_id' => $history->id, 'error' => $exception->getMessage()]);
            }
        }

        if (! $itemDCode) {
            $history->update(['status' => 'pending']);
            return;
        }

        $directLink = $service->buildDirectDownloadLink($itemDCode);

        $history->update([
            'direct_download_link' => $directLink,
            'item_d_code' => $itemDCode,
            'status' => 'ready',
        ]);

        ProcessDownloadedResource::dispatch($history);
    }
}
