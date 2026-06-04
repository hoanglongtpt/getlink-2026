@extends('layouts.admin')

@section('header_title', 'Sửa provider')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-edit text-blue-600"></i> Sửa provider: {{ $provider->slug }}
            </h2>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.download-providers.update', $provider) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
                    <input id="slug" type="text" value="{{ $provider->slug }}" class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3" disabled>
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-semibold text-gray-700 mb-2">Tên hiển thị</label>
                    <input id="display_name" name="display_name" type="text" value="{{ old('display_name', $provider->display_name) }}" class="w-full rounded-lg border border-gray-300 p-3 focus:border-purple-500 focus:ring-purple-200 focus:ring-2 outline-none" required>
                </div>

                <div>
                    <label for="xu_cost" class="block text-sm font-semibold text-gray-700 mb-2">Giá Xu</label>
                    <div class="flex gap-3 items-center">
                        <input id="xu_cost" name="xu_cost" type="number" min="1" value="{{ old('xu_cost', $provider->xu_cost) }}" class="w-full rounded-lg border border-gray-300 p-3 focus:border-purple-500 focus:ring-purple-200 focus:ring-2 outline-none" required>
                        <span class="text-yellow-600 font-semibold">Xu</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $provider->is_active) ? 'checked' : '' }} class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="is_active" class="text-sm text-gray-700">Kích hoạt provider này</label>
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
                    <p class="font-semibold">Lưu ý</p>
                    <p class="mt-2">Khi tải tài nguyên, hệ thống sẽ trừ Xu theo provider này nếu tài nguyên trả về provSlug khớp. Nếu provider chưa tồn tại, hệ thống sẽ tạo mặc định với 1 Xu.</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-5 py-3 text-white hover:bg-blue-700 transition">Lưu</button>
                    <a href="{{ route('admin.download-providers') }}" class="flex-1 rounded-lg bg-gray-200 px-5 py-3 text-gray-700 hover:bg-gray-300 transition text-center">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
