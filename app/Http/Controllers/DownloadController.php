<?php

namespace App\Http\Controllers;

use App\Models\DownloadHistory;
use App\Models\Resource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    public function index()
    {
        return view('downloads.index');
    }

    public function store(Request $request)
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

        $resource = Resource::where('original_link', $request->input('link'))->first();

        if ($resource && $resource->google_drive_link) {
            $resource->increment('download_count');
            DownloadHistory::create([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
                'original_link' => $resource->original_link,
                'direct_download_link' => $resource->google_drive_link,
                'xu_cost' => $downloadFee,
                'status' => 'cached',
                'provider' => $resource->provider,
            ]);

            $user->decrement('xu_balance', $downloadFee);

            return redirect()->route('downloads.index')->with('success', 'Tài nguyên đã có sẵn. Link Drive sẽ được gửi về.');
        }

        // TODO: call Getstock API and dispatch background upload job.
        $user->decrement('xu_balance', $downloadFee);

        $history = DownloadHistory::create([
            'user_id' => $user->id,
            'original_link' => $request->input('link'),
            'xu_cost' => $downloadFee,
            'status' => 'pending',
        ]);

        return redirect()->route('downloads.index')->with('success', 'Yêu cầu tải được chấp nhận. Hệ thống đang xử lý trong nền.');
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
