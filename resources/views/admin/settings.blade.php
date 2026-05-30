@extends('layouts.admin')

@section('header_title', 'Cài đặt hệ thống')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Box 1: Cấu hình chung -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-coins text-purple-600"></i> Cấu hình chung
            </h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="download_fee" class="block text-sm font-medium text-gray-700 mb-1">Chi phí mỗi lượt tải (Xu)</label>
                    <div class="relative">
                        <input id="download_fee" name="download_fee" type="number" value="{{ old('download_fee', $downloadFee) }}" class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-purple-500 focus:ring focus:ring-purple-200 transition outline-none" required min="0">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium text-sm">Xu</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Số lượng Xu người dùng bị trừ mỗi khi gửi request tải tài nguyên.</p>
                </div>
                <button type="submit" class="rounded-lg bg-purple-600 px-5 py-2.5 text-white font-medium hover:bg-purple-700 transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-save"></i> Lưu cài đặt
                </button>
            </form>
        </div>
    </div>

    <!-- Box 2: Google Drive OAuth -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fab fa-google-drive text-blue-500"></i> Kết nối Google Drive (OAuth 2.0)
            </h2>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Kết nối Google Drive bằng tài khoản Google cá nhân/doanh nghiệp thay vì Service Account. Yêu cầu cấu hình <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500 text-xs">GOOGLE_CLIENT_ID</code> trong <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500 text-xs">.env</code>.</p>

            @if(! empty($googleDriveEmail))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 mb-4 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-green-800">Đã kết nối tài khoản:</p>
                        <p class="text-sm text-green-700 font-bold">{{ $googleDriveEmail }}</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.google.drive.connect') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-500 px-5 py-2.5 text-white font-medium hover:bg-yellow-600 transition shadow-sm">
                        <i class="fas fa-sync-alt"></i> Kết nối lại tài khoản khác
                    </a>
                </div>
            @else
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 mb-4 flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    <div>
                        <p class="text-sm text-yellow-800">Chưa kết nối tài khoản Google Drive nào.</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.google.drive.connect') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-white font-medium hover:bg-blue-700 transition shadow-sm">
                        <i class="fas fa-link"></i> Tiến hành kết nối Google Drive
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Box 3: Google Service Account (Tùy chọn) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-key text-gray-600"></i> Upload Google Service Account JSON
            </h2>
        </div>
        <div class="p-6 flex flex-col md:flex-row gap-6 items-start">
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Phương thức thay thế cho OAuth. Tải lên tệp JSON credentials của Google Cloud Service Account.</p>
                <p class="text-xs text-red-500 italic mb-4">* Lưu ý: Service Account bị giới hạn dung lượng 15GB trừ khi bạn share vào một Shared Drive (Google Workspace).</p>
                
                <form action="{{ route('admin.google.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <input type="file" name="google_service_account" accept=".json" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                    </div>
                    <button type="submit" class="rounded-lg bg-gray-800 px-5 py-2.5 text-white font-medium hover:bg-gray-900 transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-upload"></i> Upload File Key
                    </button>
                </form>
            </div>
            
            <div class="flex-1 bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Trạng thái tệp Service Account:</h4>
                @if(file_exists(storage_path('app/google-service-account.json')))
                    <p class="text-sm text-green-600 font-medium flex items-center gap-2"><i class="fas fa-check"></i> Đã có tệp key trên hệ thống</p>
                @else
                    <p class="text-sm text-gray-500 flex items-center gap-2"><i class="fas fa-times"></i> Chưa có tệp key nào được upload</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
