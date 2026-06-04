@extends('layouts.admin')

@section('header_title', 'Quản lý provider tải xu')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tags text-purple-600"></i> Provider Xu
                </h2>
                <p class="text-sm text-gray-500">Danh sách các provSlug từ Getstock và giá xu tương ứng.</p>
            </div>
            <form method="GET" action="{{ route('admin.download-providers') }}" class="flex gap-2 items-center">
                <input name="search" value="{{ request('search') }}" type="text" placeholder="Tìm theo slug hoặc tên" class="rounded-lg border border-gray-300 px-4 py-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none" />
                <button type="submit" class="rounded-lg bg-purple-600 text-white px-4 py-2 hover:bg-purple-700 transition">Tìm</button>
            </form>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
            <p class="font-semibold">Hướng dẫn nhanh</p>
            <p class="mt-2">Dán JSON response từ API Getstock vào dưới đây để đồng bộ tất cả provSlug. Mỗi provSlug mặc định được tạo với giá 1 Xu. Sau đó quản lý giá tại cột "Giá Xu".</p>
        </div>

        <form action="{{ route('admin.download-providers.import') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="providers_json" class="block text-sm font-semibold text-gray-700 mb-2">JSON providers</label>
                <textarea id="providers_json" name="providers_json" rows="10" class="w-full rounded-lg border border-gray-300 p-4 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none" placeholder="Dán JSON response từ Getstock ở đây..."></textarea>
                @error('providers_json')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="rounded-lg bg-green-600 text-white px-5 py-2.5 hover:bg-green-700 transition">Import providers</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-medium">Slug</th>
                        <th class="px-6 py-4 font-medium">Tên hiển thị</th>
                        <th class="px-6 py-4 font-medium">Giá Xu</th>
                        <th class="px-6 py-4 font-medium">Trạng thái</th>
                        <th class="px-6 py-4 text-right font-medium">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $provider->slug }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $provider->display_name }}</td>
                            <td class="px-6 py-4 font-semibold text-yellow-600">{{ $provider->xu_cost }} Xu</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $provider->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $provider->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.download-providers.edit', $provider) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition text-sm">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Chưa có provider nào trong danh sách.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
