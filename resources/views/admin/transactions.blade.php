@extends('layouts.admin')

@section('header_title', 'Lịch sử giao dịch')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-money-bill-wave text-purple-600"></i> Giao dịch Web2m
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium">Mã giao dịch</th>
                    <th class="px-6 py-4 font-medium">Người nạp</th>
                    <th class="px-6 py-4 font-medium">Số tiền (VND)</th>
                    <th class="px-6 py-4 font-medium">Quy đổi (Xu)</th>
                    <th class="px-6 py-4 font-medium">Trạng thái</th>
                    <th class="px-6 py-4 font-medium">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">
                            {{ $transaction->transaction_code }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $transaction->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-green-600">
                            {{ number_format($transaction->amount_vnd) }} đ
                        </td>
                        <td class="px-6 py-4 font-bold text-yellow-600">
                            +{{ number_format($transaction->xu_amount) }} Xu
                        </td>
                        <td class="px-6 py-4">
                            @if($transaction->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Thành công
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Chưa có giao dịch nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
