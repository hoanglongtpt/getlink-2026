@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Transaction History</h1>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-700">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Amount VND</th>
                    <th class="px-4 py-3">Xu Amount</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3">{{ $transaction->transaction_code }}</td>
                        <td class="px-4 py-3">{{ $transaction->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ number_format($transaction->amount_vnd) }}</td>
                        <td class="px-4 py-3">{{ $transaction->xu_amount }}</td>
                        <td class="px-4 py-3">{{ ucfirst($transaction->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
