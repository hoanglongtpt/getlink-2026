<?php

namespace App\Http\Controllers;

use App\Services\Web2mService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected Web2mService $web2mService;

    public function __construct(Web2mService $web2mService)
    {
        $this->web2mService = $web2mService;
    }

    /**
     * Handle Web2m payment webhook
     */
    public function web2m(Request $request)
    {
        $accessToken = config('web2m.access_token');
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return response()->json(['message' => 'Access Token không được cung cấp hoặc không hợp lệ.'], 401);
        }

        $bearerToken = substr($authorizationHeader, 7);
        if ($bearerToken !== $accessToken) {
            return response()->json(['message' => 'Access Token không hợp lệ.'], 401);
        }

        Log::info('Web2m webhook received', [
            'headers' => [
                'authorization' => $authorizationHeader,
                'x_web2m_signature' => $request->header('X-Web2m-Signature'),
                'web2m_signature' => $request->header('Web2m-Signature'),
                'x_signature' => $request->header('X-Signature'),
            ],
            'payload' => $request->all(),
        ]);

        $data = $request->input('data', []);
        if (!is_array($data)) {
            return response()->json(['message' => 'Payload data không hợp lệ.'], 422);
        }

        foreach ($data as $transactionItem) {
            $description = $transactionItem['description'] ?? '';
            $transactionCode = $transactionItem['transaction_code'] ?? $transactionItem['id'] ?? null;
            $amountVnd = (int) ($transactionItem['amount'] ?? $transactionItem['amount_vnd'] ?? 0);

            if ($amountVnd <= 0) {
                Log::warning('Webhook payload missing amount_vnd or amount', ['payload' => $transactionItem]);
                continue;
            }

            $transaction = null;
            if ($transactionCode) {
                $transaction = \App\Models\Transaction::where('transaction_code', $transactionCode)->first();
            }

            if (! $transaction && preg_match('/napxugetlink(\d+)/i', $description, $matches)) {
                $transaction = \App\Models\Transaction::where('description', 'napxugetlink' . $matches[1])->latest()->first();
            }

            if (! $transaction && preg_match('/id(\d+)/i', $description, $matches)) {
                $transaction = \App\Models\Transaction::where('description', 'id' . $matches[1])->latest()->first();
            }

            if (! $transaction) {
                Log::warning('Không tìm thấy transaction tương ứng cho webhook', ['description' => $description, 'transaction_code' => $transactionCode]);
                continue;
            }

            if ($transaction->status === 'completed') {
                continue;
            }

            if ($transaction->amount_vnd !== $amountVnd) {
                Log::warning('Số tiền webhook không khớp với gói đã chọn', [
                    'expected' => $transaction->amount_vnd,
                    'received' => $amountVnd,
                    'transaction_id' => $transaction->id,
                ]);
                continue;
            }

            $xuMain = $transaction->metadata['xu_main'] ?? null;
            $xuBonus = $transaction->metadata['xu_bonus'] ?? null;
            if ($xuMain === null || $xuBonus === null) {
                $xuInfo = $this->web2mService->calculateXu($amountVnd);
                $xuMain = $xuInfo['xu_main'];
                $xuBonus = $xuInfo['xu_bonus'];
            }

            $transaction->status = 'completed';
            $transaction->xu_amount = $xuMain + $xuBonus;
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'web2m_payload' => $transactionItem,
                'xu_main' => $xuMain,
                'xu_bonus' => $xuBonus,
                'completed_at' => now()->toIso8601String(),
            ]);
            $transaction->save();

            $user = $transaction->user;
            if ($user) {
                $user->increment('xu_balance', $xuMain);
                $user->increment('bonus_xu', $xuBonus);
            }
        }

        return response()->json([
            'status' => true,
            'msg' => 'Ok',
        ]);
    }
}
