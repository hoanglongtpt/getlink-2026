@extends('layouts.admin')

@section('header_title', 'Tài nguyên đã Cache')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-box-open text-purple-600"></i> File đã tải lên Google Drive
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium w-1/2">Link gốc (Original)</th>
                    <th class="px-6 py-4 font-medium">Trạng thái</th>
                    <th class="px-6 py-4 font-medium text-center">Lượt tải</th>
                    <th class="px-6 py-4 font-medium text-right">Link Drive</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($resources as $resource)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-link text-gray-400"></i>
                                <div class="truncate max-w-sm md:max-w-md lg:max-w-lg text-gray-700" title="{{ $resource->original_link }}">
                                    {{ $resource->original_link }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">Lưu lúc: {{ $resource->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($resource->status === 'cached')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Đã Cache
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($resource->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-gray-700">
                            {{ $resource->download_count }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($resource->google_drive_link)
                                <a href="{{ $resource->google_drive_link }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-xs font-medium" target="_blank">
                                    <i class="fab fa-google-drive"></i> Mở Drive
                                </a>
                            @else
                                <span class="text-gray-400 text-xs italic">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-3 text-gray-300 block"></i>
                            Chưa có tài nguyên nào được lưu trữ.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($resources->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $resources->links() }}
        </div>
    @endif
</div>
@endsection
