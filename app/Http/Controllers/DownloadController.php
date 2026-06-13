<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDownloadedResource;
use App\Jobs\ProcessGetstockDownload;
use App\Models\DownloadHistory;
use App\Models\DownloadProvider;
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
        $providers = DownloadProvider::where('is_active', true)
            ->select('download_providers.*')
            ->selectSub(
                DownloadHistory::selectRaw('COUNT(*)')
                    ->whereColumn('download_histories.provider', 'download_providers.slug'),
                'downloads_count'
            )
            ->orderByDesc('downloads_count')
            ->orderBy('display_name')
            ->get();

        return view('downloads.index', compact('histories', 'downloadFee', 'providers'));
    }

        public function store(Request $request, GetstockService $getstockService, \App\Services\GoogleDriveService $driveService)
    {
        $request->validate([
            'link' => 'required|url',
            'ispre' => 'required|in:0,1',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $link = trim((string) $request->input('link'));
        // Normalize link: treat trailing-slash and non-trailing as identical
        $normalizedLink = rtrim($link, '/');
        $isPre = (int) $request->input('ispre');

        $resource = Resource::where('original_link', $normalizedLink)->first();

        // Xử lý Cache Hit
        if ($resource && filled($resource->google_drive_link)) {
            $downloadFee = DownloadProvider::getCostForSlug($resource->provider);

            if (! $user->hasSufficientXu($downloadFee)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Số dư xu không đủ để tải tài nguyên.']);
                }
                return back()->withErrors(['balance' => 'Số dư xu không đủ để tải tài nguyên.']);
            }

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
                'xu_source' => $xuSource,
                'status' => 'cached',
                'provider' => $resource->provider,
            ]);

            // Nếu share thành công, lên lịch thu hồi quyền sau 30 phút
            if (! empty($shareData['permission_id'])) {
                \App\Jobs\RevokeGoogleDrivePermission::dispatch($fileId, $shareData['permission_id'])
                    ->delay(now()->addMinutes(30));
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tài nguyên đã có sẵn trong Cache. Quyền truy cập 30 phút đã được cấp cho email của bạn!',
                    'history' => $history,
                    'new_balance' => $user->xu_balance,
                ]);
            }

            return redirect()->route('downloads.index')->with('success', 'Tài nguyên đã có sẵn. Link Drive sẽ được gửi về.');
        }

        // Tạo bản ghi chờ
        $history = DownloadHistory::create([
            'user_id' => $user->id,
            'original_link' => $normalizedLink,
            'xu_cost' => 0,
            'status' => 'pending',
            'is_premium' => $isPre === 1,
        ]);

        try {
            // Lấy Info cơ bản siêu nhanh
            $info = $getstockService->getInfo($link, $isPre);
            $type = $getstockService->firstSupportedType($info);

            $getLinkResponse = $getstockService->getLink($link, $isPre, $type);
            $slug = data_get($getLinkResponse, 'result.provSlug');
            $itemId = data_get($getLinkResponse, 'result.itemID');
            $type = $type ?: data_get($getLinkResponse, 'result.itemType');

            if (! $slug || ! $itemId || ! $type) {
                throw new \RuntimeException('Getstock response missing required download references.');
            }

            // Use provType (stored in $type) as provider key because it represents specific variant
            $providerKey = $type ?: $slug;
            $provider = DownloadProvider::findOrCreateBySlug($providerKey, data_get($getLinkResponse, 'result.provName') ?: $providerKey);
            $downloadFee = $provider->xu_cost;

            if (! $user->hasSufficientXu($downloadFee)) {
                $history->update([
                    'getstock_slug' => $slug,
                    'provider' => $slug,
                    'xu_cost' => $downloadFee,
                    'status' => 'failed',
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Số dư xu không đủ để tải tài nguyên theo provider này.']);
                }

                return back()->withErrors(['balance' => 'Số dư xu không đủ để tải tài nguyên theo provider này.']);
            }

            $xuSource = $user->deductXu($downloadFee);
            $history->update([
                'getstock_slug' => $slug,
                'getstock_item_id' => $itemId,
                'getstock_type' => $type,
                // store provider as provType (or providerKey) so pricing lookup matches
                'provider' => $providerKey,
                'xu_cost' => $downloadFee,
                'xu_source' => $xuSource,
                'status' => 'processing', // Chuyển sang đang xử lý ngầm
            ]);

            // Đẩy polling Getstock vào queue để request không bị treo khi API xử lý lâu.
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
            $user->refundXu($downloadFee ?? 0, $xuSource ?? 'balance');

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
