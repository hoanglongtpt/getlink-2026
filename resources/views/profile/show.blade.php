@extends('layouts.app')

@section('header_title', 'Hồ sơ cá nhân')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="grid gap-8 lg:grid-cols-4">
        <!-- Sidebar hồ sơ -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white flex items-center justify-center font-bold text-3xl shadow-md mb-4">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h1 class="text-xl font-bold text-gray-800">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>
                
                <div class="w-full bg-purple-50 rounded-xl p-4 border border-purple-100">
                    <p class="text-xs text-purple-600 font-medium uppercase tracking-wider mb-1">Số dư hiện tại</p>
                    <p class="text-2xl font-black text-purple-800 xu-balance-display">{{ $user->xu_balance }} Xu</p>
                    @if($user->bonus_xu > 0)
                        <p class="text-sm text-orange-500 font-bold mt-1">+{{ $user->bonus_xu }} Xu Bonus</p>
                    @endif
                </div>
                
                <a href="{{ route('packages.index') }}" class="w-full mt-4 bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-coins"></i> Nạp thêm Xu
                </a>
            </div>
            
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Thông tin khác</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between">
                        <span class="text-gray-500">Vai trò:</span>
                        <span class="font-medium text-gray-800">{{ $user->isAdmin() ? 'Quản trị viên' : 'Thành viên' }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-500">Tham gia:</span>
                        <span class="font-medium text-gray-800">{{ $user->created_at->format('d/m/Y') }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-500">Tổng tải:</span>
                        <span class="font-medium text-gray-800">{{ $histories->count() }} file</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Nội dung chính - Lịch sử tải -->
        <div class="lg:col-span-3 space-y-6" id="history">
            <div class="rounded-2xl bg-white shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full min-h-[500px]">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 gap-4">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-history text-purple-600"></i> Lịch sử tải tài nguyên
                    </h2>
                    <a href="{{ route('downloads.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tải file mới
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-medium">Link Gốc</th>
                                <th class="px-6 py-4 font-medium">Trạng thái</th>
                                <th class="px-6 py-4 font-medium">Chi phí</th>
                                <th class="px-6 py-4 font-medium text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white" id="historyTableBody">
                            @forelse($histories as $history)
                                <tr data-id="{{ $history->id }}" data-status="{{ $history->status }}" class="hover:bg-purple-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center max-w-[200px] sm:max-w-xs md:max-w-sm">
                                            <div class="truncate text-gray-800 font-medium" title="{{ $history->original_link }}">
                                                {{ $history->original_link }}
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                            <i class="far fa-clock"></i> {{ $history->created_at->format('d/m/Y H:i') }}
                                            @if($history->provider)
                                                <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px]">{{ $history->provider }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 status-cell">
                                        @if($history->status === 'completed' || $history->status === 'cached' || $history->status === 'ready')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle"></i> Thành công
                                            </span>
                                        @elseif($history->status === 'failed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                <i class="fas fa-times-circle"></i> Thất bại
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-circle-notch fa-spin"></i> Đang tải...
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-medium">
                                        <span class="{{ $history->status === 'failed' ? 'line-through text-gray-400' : '' }}">{{ $history->xu_cost }} Xu</span>
                                    </td>
                                    <td class="px-6 py-4 text-right action-cell">
                                        @if($history->direct_download_link)
                                            <a href="{{ $history->direct_download_link }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-bold shadow-sm">
                                                <i class="fas fa-download"></i> Tải về
                                            </a>
                                        @elseif($history->status === 'failed')
                                            <span class="text-xs text-red-500 italic">Đã hoàn xu</span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-500 rounded-lg text-sm font-medium cursor-not-allowed">
                                                <i class="fas fa-hourglass-half"></i> Chờ...
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                                                <i class="fas fa-folder-open text-3xl text-gray-300"></i>
                                            </div>
                                            <p class="text-lg font-medium text-gray-600 mb-1">Chưa có lịch sử tải</p>
                                            <p class="text-sm mb-4">Bạn chưa tải tài nguyên nào trên hệ thống.</p>
                                            <a href="{{ route('downloads.index') }}" class="px-5 py-2.5 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition-colors shadow-sm">
                                                Tải file đầu tiên
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Polling logic specifically for history page
    document.addEventListener('DOMContentLoaded', function() {
        const pendingRows = document.querySelectorAll('tr[data-status="pending"], tr[data-status="processing"], tr[data-status="ready"]');
        
        if (pendingRows.length === 0) return;
        
        const idsToPoll = Array.from(pendingRows).map(row => row.dataset.id);
        
        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch('/download/poll-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids: idsToPoll })
                });
                
                const updatedHistories = await response.json();
                let allDone = true;
                
                updatedHistories.forEach(history => {
                    const row = document.querySelector(`tr[data-id="${history.id}"]`);
                    if (!row) return;
                    
                    if (history.status === 'completed' || history.status === 'cached' || history.status === 'ready' || history.status === 'failed') {
                        // Reload page when a download finishes so the table gets fully updated with right icons/links
                        window.location.reload();
                    } else {
                        allDone = false;
                    }
                });
                
                if (allDone) clearInterval(pollInterval);
            } catch (e) {
                console.error('Polling error', e);
            }
        }, 3000);
    });
</script>
@endsection