<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'top_up')
            ->latest()
            ->take(5)
            ->get();

        return view('packages.index', compact('recentTransactions'));
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
