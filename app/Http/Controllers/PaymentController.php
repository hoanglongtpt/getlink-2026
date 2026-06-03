<?php

namespace App\Http\Controllers;

use App\Services\Web2mService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected Web2mService $web2mService;

    public function __construct(Web2mService $web2mService)
    {
        $this->web2mService = $web2mService;
    }

    /**
     * Hiển thị trang gói thanh toán
     */
    public function showPackages()
    {
        $packages = $this->web2mService->getPackageInfo();
        $user = Auth::user();
        $web2mDetails = [
            'bank_name' => config('web2m.bank_name'),
            'bank_code' => config('web2m.bank_code'),
            'account_number' => config('web2m.account_number'),
            'account_holder' => config('web2m.account_holder'),
            'transfer_content_prefix' => config('web2m.transfer_content_prefix', 'id'),
        ];

        return view('packages.index', [
            'packages' => $packages,
            'user' => $user,
            'web2mDetails' => $web2mDetails,
        ]);
    }

    /**
     * Khởi tạo thanh toán - chuyển hướng tới Web2m
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount_vnd' => 'required|integer|min:1000',
        ]);

        $user = Auth::user();
        $amountVnd = $request->integer('amount_vnd');

        $package = $this->web2mService->findPackageByAmount($amountVnd);
        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Gói nạp không hợp lệ hoặc đã bị thay đổi. Vui lòng thử lại.',
            ], 422);
        }

        $orderCode = intval(substr(strval(microtime(true) * 10000), -6));
        $transferPrefix = config('web2m.transfer_content_prefix', 'id');
        $description = $transferPrefix . $user->id;

        $xuMain = $package['xu_main'];
        $xuBonus = $package['xu_bonus'];
        $totalXu = $xuMain + $xuBonus;

        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'transaction_code' => (string) $orderCode,
            'description' => $description,
            'amount_vnd' => $amountVnd,
            'xu_amount' => $totalXu,
            'status' => 'pending',
            'payment_method' => 'web2m',
            'type' => 'top_up',
            'metadata' => [
                'package_name' => $package['name'] ?? null,
                'xu_main' => $xuMain,
                'xu_bonus' => $xuBonus,
                'initiated_at' => now()->toIso8601String(),
            ],
        ]);

        $qrUrl = $this->web2mService->generateQRWeb2mQr(
            config('web2m.bank_code'),
            config('web2m.account_number'),
            $amountVnd,
            $description
        );

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'qr_url' => $qrUrl,
            'amount_vnd' => $amountVnd,
            'xu_main' => $xuMain,
            'xu_bonus' => $xuBonus,
            'total_xu' => $totalXu,
        ]);
    }

    /**
     * Kiểm tra trạng thái thanh toán
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        
        // Lấy giao dịch top_up mới nhất trong vòng 10 phút
        $latestTransaction = \App\Models\Transaction::where('user_id', $user->id)
            ->where('type', 'top_up')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();

        return response()->json([
            'xu_balance' => $user->xu_balance,
            'bonus_xu' => $user->bonus_xu,
            'total_xu' => $user->xu_balance + $user->bonus_xu,
            'latest_transaction' => $latestTransaction ? [
                'id' => $latestTransaction->id,
                'amount_vnd' => $latestTransaction->amount_vnd,
                'xu_amount' => $latestTransaction->xu_amount,
                'status' => $latestTransaction->status,
                'created_at' => $latestTransaction->created_at->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Lấy thông tin gói thanh toán (API)
     */
    public function getPackages()
    {
        $packages = $this->web2mService->getPackageInfo();
        
        return response()->json([
            'packages' => $packages,
            'currency' => 'VND',
            'pricing_table' => $this->web2mService->getPricingTable(),
        ]);
    }
}
