@extends('layouts.app')

@section('header_title', 'Download Resource')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Download Resource</h1>
        <p class="text-sm text-gray-600 mt-1">Sử dụng link Getstock để tải tài nguyên nhanh chóng</p>
    </div>
    <div class="rounded-lg bg-white px-5 py-3 shadow-sm border border-gray-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
            <i class="fas fa-info-circle text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Chi phí tải</p>
            <p class="text-sm text-gray-800 font-bold">{{ $downloadFee }} Xu <span class="text-gray-500 font-normal">/ request</span></p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start gap-3">
        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
        <div class="text-green-800 font-medium">{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
        <div class="text-red-800 text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Sidebar Cột Trái -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Banner Hình Ảnh Giới Thiệu Giá -->
        <div class="rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden relative group cursor-pointer">
            <!-- Thay thế link src bên dưới bằng link ảnh thật của bạn (VD: /images/banner.jpg hoặc link URL) -->
            <img src="https://placehold.co/600x250/f3e8ff/6b21a8?text=Banner+Gi%C3%A1+L%C6%B0%E1%BB%A3t+T%E1%BA%A3i" alt="Bảng giá lượt tải" class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                <span class="text-white text-sm font-medium drop-shadow-md">Xem chi tiết bảng giá <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
        </div>

        <!-- Form Box -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center gap-2">
                <i class="fas fa-link text-purple-600"></i> New Download
            </h2>
            <form method="POST" action="{{ route('downloads.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Resource link</label>
                    <input id="link" name="link" type="url" value="{{ old('link') }}" placeholder="https://..." class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-purple-500 focus:ring focus:ring-purple-200 transition outline-none" required>
                </div>
                <div>
                    <label for="ispre" class="block text-sm font-medium text-gray-700 mb-1">Resource type</label>
                    <select id="ispre" name="ispre" class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-purple-500 focus:ring focus:ring-purple-200 transition outline-none bg-white" required>
                        <option value="0"{{ old('ispre') === '0' ? ' selected' : '' }}>Normal</option>
                        <option value="1"{{ old('ispre') === '1' ? ' selected' : '' }}>Premium</option>
                    </select>
                </div>
                                <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-purple-600 to-purple-800 px-4 py-2.5 text-white font-medium hover:from-purple-700 hover:to-purple-900 transition shadow-md flex items-center justify-center gap-2" {{ !Auth::check() ? 'disabled' : '' }}>
                    <i class="fas fa-cloud-download-alt"></i> {{ Auth::check() ? 'Submit Download' : 'Vui lòng đăng nhập để tải' }}
                </button>
            </form>
        </div>
    </div>

    <!-- History Box -->
    <div class="lg:col-span-2">
        <!-- Đặt chiều cao tối đa (max-h) và cho phép scroll nội dung bảng -->
        <div class="rounded-xl bg-white p-0 shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full max-h-[480px]">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-history text-purple-600"></i> Recent History
                </h2>
            </div>
            <div class="overflow-y-auto flex-1 relative custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <!-- Thêm sticky top-0 để giữ header cố định khi cuộn -->
                    <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 font-medium">Link</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium">Cost</th>
                            <th class="px-6 py-4 font-medium">Result</th>
                        </tr>
                    </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                        @if(!Auth::check())
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-lock text-4xl mb-3 text-gray-300"></i>
                                        <p class="mb-2">Vui lòng đăng nhập để xem lịch sử tải về của bạn.</p>
                                        <a href="{{ url('/login') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm">Đăng nhập ngay</a>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @forelse($histories as $history)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center max-w-[200px] sm:max-w-xs md:max-w-sm">
                                        <div class="truncate text-gray-800" title="{{ $history->original_link }}">
                                            {{ $history->original_link }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $history->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($history->status === 'completed' || $history->status === 'cached' || $history->status === 'ready')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ ucfirst($history->status) }}
                                        </span>
                                    @elseif($history->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($history->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ $history->xu_cost }} Xu
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($history->direct_download_link)
                                        <a href="{{ $history->direct_download_link }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition text-xs font-medium" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Processing...</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                        <p>No download history found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Box Thẻ SEO (Full Width) -->
<div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 mb-6">
    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-hashtag text-purple-600"></i> Khám phá từ khóa
    </h3>
    <div class="flex flex-wrap gap-2">
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Getstock Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Freepik Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Envato Elements</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Motion Array</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Pikbest</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Tài Khoản Giá Rẻ</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Tải Ảnh Nhanh</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Vector Đẹp</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-md border border-gray-200 hover:border-purple-200 transition-colors">#Mua Chung Tài Khoản</a>
    </div>
    <p class="text-[11px] text-gray-400 mt-4 italic">* Các thẻ này giúp tăng cường SEO và điều hướng tìm kiếm.</p>
</div>

<!-- CSS cho thanh cuộn đẹp hơn (tùy chọn) -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1; 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8; 
    }
</style>
@endsection
