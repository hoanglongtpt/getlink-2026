<?php

namespace App\Http\Controllers;

use App\Services\Web2mService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    protected Web2mService $web2mService;

    public function __construct(Web2mService $web2mService)
    {
        $this->web2mService = $web2mService;
    }

    public function index()
    {
        $user = Auth::user();
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'top_up')
            ->latest()
            ->take(5)
            ->get();

        $web2mDetails = [
            'bank_name' => config('web2m.bank_name', 'MB-BANK'),
            'bank_code' => config('web2m.bank_code', 'MBB'),
            'account_number' => config('web2m.account_number', '9999928071998'),
            'account_holder' => config('web2m.account_holder', 'PHAM XUAN QUY'),
            'transfer_content_prefix' => config('web2m.transfer_content_prefix', 'id'),
        ];

        $packages = $this->web2mService->getPackageInfo();

        return view('packages.index', compact('recentTransactions', 'web2mDetails', 'packages'));
    }

    public function status()
    {
        $user = Auth::user();
        // Lấy giao dịch mới nhất trong vòng 1 phút qua hoặc các giao dịch pending
        $latest = Transaction::where('user_id', $user->id)
            ->where('type', 'top_up')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();

        return response()->json([
            'xu_balance' => $user->xu_balance,
            'bonus_xu' => $user->bonus_xu,
            'latest_transaction' => $latest
        ]);
    }
}
