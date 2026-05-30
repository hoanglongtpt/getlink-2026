<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDownloadedResource;
use App\Jobs\ProcessGetstockDownload;
use App\Models\DownloadHistory;
use App\Models\Resource;
use App\Models\Setting;
use App\Services\GetstockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
        public function index()
    {
        $user = Auth::user();
        
        $histories = collect();
        if ($user) {
            $histories = DownloadHistory::where('user_id', $user->id)->latest()->take(10)->get();
        }
        
        $downloadFee = (int) Setting::getValue('download_fee', 10);

        return view('downloads.index', compact('histories', 'downloadFee'));
    }

    public function store(Request $request, GetstockService $getstockService)
    {
        $request->validate([
            'link' => 'required|url',
            'ispre' => 'required|in:0,1',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $downloadFee = (int) Setting::getValue('download_fee', 10);

        if (! $user->hasSufficientXu($downloadFee)) {
            return back()->withErrors(['balance' => 'Số dư xu không đủ để tải tài nguyên.']);
        }

        $link = $request->input('link');
        $isPre = (int) $request->input('ispre');

        $resource = Resource::where('original_link', $link)->first();

        if ($resource && filled($resource->google_drive_link)) {
            $resource->increment('download_count');
            $user->decrement('xu_balance', $downloadFee);

            DownloadHistory::create([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
                'original_link' => $resource->original_link,
                'direct_download_link' => $resource->google_drive_link,
                'xu_cost' => $downloadFee,
                'status' => 'cached',
                'provider' => $resource->provider,
            ]);

            return redirect()->route('downloads.index')->with('success', 'Tài nguyên đã có sẵn. Link Drive sẽ được gửi về.');
        }

        $history = DownloadHistory::create([
            'user_id' => $user->id,
            'original_link' => $link,
            'xu_cost' => $downloadFee,
            'status' => 'pending',
            'is_premium' => $isPre === 1,
        ]);

        $user->decrement('xu_balance', $downloadFee);

        try {
            $info = $getstockService->getInfo($link, $isPre);
            $type = null;

            if (! empty($info['result']['support']['type'][0])) {
                $type = $info['result']['support']['type'][0];
            }

            $getLinkResponse = $getstockService->getLink($link, $isPre, $type);
            $slug = data_get($getLinkResponse, 'result.provSlug');
            $itemId = data_get($getLinkResponse, 'result.itemID');
            $type = $type ?: data_get($getLinkResponse, 'result.itemType');

            if (! $slug || ! $itemId || ! $type) {
                throw new \RuntimeException('Getstock response missing required download references.');
            }

            $history->update([
                'getstock_slug' => $slug,
                'getstock_item_id' => $itemId,
                'getstock_type' => $type,
                'status' => 'processing',
            ]);

            $statusResult = null;
            for ($attempt = 0; $attempt < 4; $attempt++) {
                sleep(5);
                $statusResponse = $getstockService->checkDownloadStatus($slug, $itemId, $isPre, $type);
                if (data_get($statusResponse, 'result.status') === 1 && data_get($statusResponse, 'result.itemDCode')) {
                    $statusResult = $statusResponse;
                    break;
                }
            }

            if ($statusResult) {
                $itemDCode = data_get($statusResult, 'result.itemDCode');
                $directLink = $getstockService->buildDirectDownloadLink($itemDCode);

                $history->update([
                    'direct_download_link' => $directLink,
                    'item_d_code' => $itemDCode,
                    'status' => 'ready',
                ]);

                ProcessDownloadedResource::dispatch($history);

                return redirect()->route('downloads.index')->with('success', 'Download is ready. Use the direct link from your history entry.');
            }

            ProcessGetstockDownload::dispatch($history);

            return redirect()->route('downloads.index')->with('success', 'Download request is accepted. Processing in background.');
        } catch (\Throwable $exception) {
            Log::error('Getstock error', ['error' => $exception->getMessage(), 'user_id' => $user->id, 'link' => $link]);
            $history->update(['status' => 'failed']);
            $user->increment('xu_balance', $downloadFee);

            return back()->withErrors(['download' => 'Unable to process the download request at this time. Please try again later.']);
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            'download_history_id' => 'required|exists:download_histories,id',
        ]);

        $history = DownloadHistory::findOrFail($request->input('download_history_id'));

        return response()->json([
            'status' => $history->status,
            'direct_link' => $history->direct_download_link,
        ]);
    }
}
