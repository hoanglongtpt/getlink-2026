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

        if (! isset($payload['transaction_code']) || ! isset($payload['amount_vnd']) || ! isset($payload['email'])) {
            return response()->json(['message' => 'Invalid webhook payload'], 422);
        }

        $user = User::where('email', $payload['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $xuAmount = (int) ($payload['amount_vnd'] / 1000);

        $transaction = Transaction::updateOrCreate([
            'transaction_code' => $payload['transaction_code'],
        ], [
            'user_id' => $user->id,
            'amount_vnd' => $payload['amount_vnd'],
            'xu_amount' => $xuAmount,
            'status' => 'completed',
            'payment_method' => 'web2m',
            'type' => 'top_up',
            'metadata' => $payload,
        ]);

        $user->increment('xu_balance', $xuAmount);

        return response()->json(['message' => 'Balance updated']);
    }
}
