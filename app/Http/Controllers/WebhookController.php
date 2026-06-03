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

        $data = $request->input('data', []);
        if (!is_array($data)) {
            return response()->json(['message' => 'Payload data không hợp lệ.'], 422);
        }

        foreach ($data as $transactionItem) {
            $description = $transactionItem['description'] ?? '';
            $idCode = null;

            if (preg_match('/napxugetlink(\d+)/i', $description, $matches)) {
                $idCode = 'napxugetlink' . $matches[1];
            } elseif (preg_match('/id(\d+)/i', $description, $matches)) {
                $idCode = 'id' . $matches[1];
            }

            if (! $idCode) {
                Log::warning("Không tìm thấy mã napxugetlink/id trong description: {$description}");
                continue;
            }

            $transaction = \App\Models\Transaction::where('description', $idCode)
                ->orderByDesc('id')
                ->first();

            if (! $transaction) {
                Log::warning("Không tìm thấy transaction cho mã {$idCode}");
                continue;
            }

            if ($transaction->status === 'completed') {
                continue;
            }

            $amountVnd = (int) ($transactionItem['amount'] ?? 0);
            if ($transaction->amount_vnd !== $amountVnd) {
                Log::warning("Số tiền webhook ({$amountVnd}) không khớp với gói đăng ký ({$transaction->amount_vnd}) cho mã {$idCode}");
                continue;
            }

            $xuInfo = $this->web2mService->calculateXu($amountVnd);
            $totalXu = $xuInfo['xu_main'] + $xuInfo['xu_bonus'];

            $transaction->status = 'completed';
            $transaction->xu_amount = $totalXu;
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'web2m_payload' => $transactionItem,
                'xu_main' => $xuInfo['xu_main'],
                'xu_bonus' => $xuInfo['xu_bonus'],
            ]);
            $transaction->save();

            $user = $transaction->user;
            if ($user) {
                $user->increment('xu_balance', $xuInfo['xu_main']);
                $user->increment('bonus_xu', $xuInfo['xu_bonus']);
            }
        }

        return response()->json([
            'status' => true,
            'msg' => 'Ok',
        ]);
    }
}
