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
        // TODO: Validate the Web2m webhook signature and payload.
        $payload = $request->all();

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
