@extends('layouts.app')

@section('header_title', 'Download Resource')

@section('content')

@if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start gap-3 shadow-sm">
        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
        <div class="text-green-800 font-medium">{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3 shadow-sm">
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

<!-- Download Section -->
<div class="flex flex-col items-center justify-center mb-12 mt-10">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-3 tracking-tight">Tải Tài Nguyên Premium</h1>
        <p class="text-gray-500 max-w-3xl mx-auto text-sm md:text-base">
            GETLINK NHANH - GIÁ SIÊU RẺ - CHỈ TỪ 1,000đ<br>
            Chỉ cần dán link file muốn tải và nhấn Download, hệ thống sẽ tự động trả file sau vài giây.
        </p>
    </div>

    <div class="w-full max-w-4xl relative">
        <div class="absolute -inset-1.5 bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl blur opacity-30"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl border border-purple-100 p-3 sm:p-4">
            <form id="downloadForm" method="POST" action="{{ route('downloads.store') }}" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <input type="hidden" name="ispre" value="1">
                
                <div class="w-full relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-link text-gray-400 text-base"></i>
                    </div>
                    <input id="link" name="link" type="url" value="{{ old('link') }}" placeholder="Dán link tài nguyên vào đây (VD: Freepik, Envato...)" class="w-full pl-12 pr-5 py-4 md:py-5 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all text-gray-800 bg-gray-50 focus:bg-white text-sm md:text-base outline-none shadow-inner" required autocomplete="off">
                </div>
                
                <button id="btnSubmit" type="submit" class="w-full sm:w-auto shrink-0 bg-gradient-to-r from-purple-600 to-purple-800 text-white font-bold py-4 md:py-5 px-8 rounded-xl hover:from-purple-700 hover:to-purple-900 transition-all shadow-lg flex items-center justify-center gap-2 text-sm md:text-base" {{ !Auth::check() ? 'disabled' : '' }}>
                    <i class="fas fa-cloud-download-alt text-lg"></i>
                    <span>{{ Auth::check() ? 'Download' : 'Đăng nhập để tải' }}</span>
                </button>
            </form>
        </div>
    </div>
    
    <div class="mt-6 flex items-center gap-4 text-sm text-gray-500">
        <div class="flex items-center gap-1.5"><i class="fas fa-bolt text-yellow-500"></i> Nhanh chóng</div>
        <div class="w-1 h-1 rounded-full bg-gray-300"></div>
        <div class="flex items-center gap-1.5"><i class="fas fa-shield-alt text-green-500"></i> An toàn</div>
        <div class="w-1 h-1 rounded-full bg-gray-300"></div>
        <div class="flex items-center gap-1.5"><i class="fas fa-coins text-orange-500"></i> Tiết kiệm</div>
    </div>
</div>

<!-- Polling Status Section (Hidden by default, shown when downloading) -->
<div id="pollingSection" class="hidden mb-12 max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 overflow-hidden relative">
        <div id="pollingProgressBar" class="absolute top-0 left-0 w-full h-1 bg-gray-100">
            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 w-1/3 animate-[shimmer_2s_infinite]"></div>
        </div>
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <div id="pollingIconWrap" class="w-16 h-16 shrink-0 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center relative">
                    <i id="pollingIcon" class="fas fa-spinner fa-spin text-3xl"></i>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 id="pollingTitle" class="text-lg font-bold text-gray-800 mb-1">Đang xử lý yêu cầu tải...</h3>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-1" id="pollingLinkDisplay">Đang chuẩn bị link...</p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                            </span>
                            <span class="text-sm font-medium text-purple-700" id="pollingStatusText">Hệ thống đang tải tài nguyên</span>
                        </div>
                        <a id="pollingResultLink" href="#" class="hidden px-5 py-2.5 bg-green-500 text-white text-sm font-bold rounded-xl hover:bg-green-600 transition-colors shadow-sm items-center gap-2" target="_blank">
                            <i class="fas fa-download"></i> Nhấp để tải file
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-6 p-4 bg-yellow-50 rounded-xl border border-yellow-100 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                <p class="text-sm text-yellow-800 leading-relaxed">Nếu bạn vô tình tải lại trang hoặc tắt tab trong lúc này, hệ thống vẫn đang xử lý. Bạn có thể tìm thấy file ở trang <a href="{{ url('/profile') }}#history" class="font-bold underline hover:text-yellow-900">Lịch sử tải</a> sau khi hoàn thành.</p>
            </div>
        </div>
    </div>
</div>

<!-- Thẻ SEO Keywords -->
<div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 mb-8 max-w-4xl mx-auto">
    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-hashtag text-purple-600"></i> Từ khóa phổ biến
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Getstock Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Freepik Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Envato Elements</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Motion Array</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Pikbest</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Tài Khoản Giá Rẻ</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Tải Ảnh Nhanh</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Vector Đẹp</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Mua Chung Tài Khoản</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Adobe Stock</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Shutterstock</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Template Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Video Stock</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Stock Music</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Ảnh Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Vector Premium</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Mockup Đẹp</a>
        <a href="#" class="inline-block px-3 py-1.5 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-700 text-xs font-medium rounded-lg border border-gray-200 hover:border-purple-200 transition-colors">#Font Premium</a>
    </div>
</div>

<style>
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(300%); }
    }
</style>
@endsection
