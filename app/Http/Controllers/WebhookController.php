<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
        public function web2m(Request $request)
    {
        $secret = env('WEB2M_WEBHOOK_SECRET');
        $payload = $request->all();

        if ($secret) {
            $signature = $request->header('X-Web2m-Signature')
                ?? $request->header('Web2m-Signature')
                ?? $request->header('X-Signature');

            if (! $signature) {
                Log::warning('Web2m webhook missing signature', $payload);

                return response()->json(['message' => 'Missing webhook signature'], 403);
            }

            $payloadJson = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $payloadJson, $secret);

            if (! hash_equals($expectedSignature, $signature)) {
                Log::warning('Web2m webhook invalid signature', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]);

                return response()->json(['message' => 'Invalid webhook signature'], 403);
            }
        } else {
            Log::warning('WEB2M_WEBHOOK_SECRET is not configured. Webhook signature was not validated.');
        }

        Log::info('Web2m webhook received', $payload);

        // Web2m thường gửi amount và description
        // Giả sử Web2m gửi email hoặc description có chứa NAPXU {ID}
        $transactionCode = $payload['transaction_code'] ?? $payload['id'] ?? null;
        $amountVnd = (int) ($payload['amount_vnd'] ?? $payload['amount'] ?? 0);
        $description = $payload['description'] ?? '';

        if (!$transactionCode || $amountVnd <= 0) {
            return response()->json(['message' => 'Invalid webhook payload'], 422);
        }

        // Tìm user từ description: NAPXU 123
        $userId = null;
        if (preg_match('/NAPXU\s+(\d+)/i', $description, $matches)) {
            $userId = $matches[1];
        }

        $user = $userId ? User::find($userId) : User::where('email', $payload['email'] ?? '')->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Logic tính toán Xu và Thưởng dựa trên bảng giá
        $xuMain = 0;
        $xuBonus = 0;

        if ($amountVnd >= 500000) {
            $xuMain = 500;
            $xuBonus = 100;
        } elseif ($amountVnd >= 200000) {
            $xuMain = 200;
            $xuBonus = 30;
        } elseif ($amountVnd >= 100000) {
            $xuMain = 100;
            $xuBonus = 10;
        } elseif ($amountVnd >= 20000) {
            $xuMain = 20;
            $xuBonus = 0;
        } else {
            // Nạp lẻ: 1000đ = 1 xu
            $xuMain = (int)($amountVnd / 1000);
            $xuBonus = 0;
        }

        $transaction = Transaction::updateOrCreate([
            'transaction_code' => $transactionCode,
        ], [
            'user_id' => $user->id,
            'amount_vnd' => $amountVnd,
            'xu_amount' => $xuMain + $xuBonus,
            'status' => 'completed',
            'payment_method' => 'web2m',
            'type' => 'top_up',
            'metadata' => array_merge($payload, ['xu_main' => $xuMain, 'xu_bonus' => $xuBonus]),
        ]);

        $user->increment('xu_balance', $xuMain);
        $user->increment('bonus_xu', $xuBonus);

        return response()->json(['message' => 'Balance updated', 'xu' => $xuMain, 'bonus' => $xuBonus]);
    }
}
