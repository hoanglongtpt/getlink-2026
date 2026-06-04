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

        public function store(Request $request, GetstockService $getstockService, \App\Services\GoogleDriveService $driveService)
    {
        $request->validate([
            'link' => 'required|url',
            'ispre' => 'required|in:0,1',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $downloadFee = (int) Setting::getValue('download_fee', 10);

        if (! $user->hasSufficientXu($downloadFee)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Số dư xu không đủ để tải tài nguyên.']);
            }
            return back()->withErrors(['balance' => 'Số dư xu không đủ để tải tài nguyên.']);
        }

        $link = trim((string) $request->input('link'));
        // Normalize link: treat trailing-slash and non-trailing as identical
        $normalizedLink = rtrim($link, '/');
        $isPre = (int) $request->input('ispre');

        $resource = Resource::where('original_link', $normalizedLink)->first();

                // Xử lý Cache Hit
        if ($resource && filled($resource->google_drive_link)) {
            $resource->increment('download_count');
            $xuSource = $user->deductXu($downloadFee);

                        // Share file cho user cụ thể khi lấy từ cache
            $fileId = $resource->google_drive_file_id;
            $shareData = $driveService->getViewerLink($fileId, $user->email);

            $history = DownloadHistory::create([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
                'original_link' => $resource->original_link,
                'direct_download_link' => $resource->google_drive_link,
                'drive_permission_id' => $shareData['permission_id'] ?? null,
                'xu_cost' => $downloadFee,
                'status' => 'cached',
                'provider' => $resource->provider,
            ]);

            // Nếu share thành công, lên lịch thu hồi quyền sau 30 phút
            if (!empty($shareData['permission_id'])) {
                \App\Jobs\RevokeGoogleDrivePermission::dispatch($fileId, $shareData['permission_id'])
                    ->delay(now()->addMinutes(30));
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tài nguyên đã có sẵn trong Cache. Quyền truy cập 30 phút đã được cấp cho email của bạn!',
                    'history' => $history,
                    'new_balance' => $user->xu_balance
                ]);
            }
            return redirect()->route('downloads.index')->with('success', 'Tài nguyên đã có sẵn. Link Drive sẽ được gửi về.');
        }

        // Tạo bản ghi chờ
        $history = DownloadHistory::create([
            'user_id' => $user->id,
            'original_link' => $normalizedLink,
            'xu_cost' => $downloadFee,
            'status' => 'pending',
            'is_premium' => $isPre === 1,
        ]);

        $xuSource = $user->deductXu($downloadFee);

        try {
            // Lấy Info cơ bản siêu nhanh
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
                'status' => 'processing', // Chuyển sang đang xử lý ngầm
            ]);

                        $statusResult = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                sleep(3);
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

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Hệ thống đã nhận link trực tiếp, đang xử lý đưa lên Drive của bạn...',
                        'history' => $history,
                        'new_balance' => $user->xu_balance
                    ]);
                }
                return redirect()->route('downloads.index')->with('success', 'Download is ready. Use the direct link from your history entry.');
            }

            // Nếu 3 lần đầu chưa xong, đẩy vào Queue
            ProcessGetstockDownload::dispatch($history);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hệ thống đang tiến hành xử lý tải tài nguyên...',
                    'history' => $history,
                    'new_balance' => $user->xu_balance
                ]);
            }
            return redirect()->route('downloads.index')->with('success', 'Download request is accepted. Processing in background.');
        } catch (\Throwable $exception) {
            Log::error('Getstock error', ['error' => $exception->getMessage(), 'user_id' => $user->id, 'link' => $link]);
            $history->update(['status' => 'failed']);
            $user->refresh();
            $user->refundXu($downloadFee, $xuSource ?? 'balance');

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Lỗi kết nối Getstock, vui lòng thử lại sau. Xu của bạn đã được hoàn lại.']);
            }
            return back()->withErrors(['download' => 'Unable to process the download request at this time. Please try again later.']);
        }
    }

    public function status(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json([]);
        }

        $histories = DownloadHistory::whereIn('id', $ids)
                        ->where('user_id', Auth::id())
                        ->get(['id', 'status', 'direct_download_link']);

        return response()->json($histories);
    }
}
