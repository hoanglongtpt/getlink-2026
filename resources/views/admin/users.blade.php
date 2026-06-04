@extends('layouts.admin')

@section('header_title', 'Quản lý người dùng')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-users text-purple-600"></i> Danh sách thành viên
            </h2>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.users') }}" class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Tìm kiếm theo email hoặc tên..." 
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
            <button 
                type="submit" 
                class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium"
            >
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
            @if(request('search'))
                <a 
                    href="{{ route('admin.users') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-medium"
                >
                    <i class="fas fa-times"></i> Xóa
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-medium">Người dùng</th>
                    <th class="px-6 py-4 font-medium">Số dư (Xu)</th>
                    <th class="px-6 py-4 font-medium">Xu Thưởng</th>
                    <th class="px-6 py-4 font-medium">Vai trò</th>
                    <th class="px-6 py-4 font-medium">Trạng thái</th>
                    <th class="px-6 py-4 font-medium text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-yellow-600">
                            {{ number_format($user->xu_balance) }} Xu
                        </td>
                        <td class="px-6 py-4 font-bold text-pink-600">
                            {{ number_format($user->bonus_xu ?? 0) }} Xu
                        </td>
                        <td class="px-6 py-4">
                            @if($user->isAdmin())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Member
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->blocked_at)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-lock"></i> Đã khóa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Hoạt động
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a 
                                    href="{{ route('admin.users.edit', $user) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                >
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                @if(Auth::guard('admin')->user()->id !== $user->id)
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $user->blocked_at ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                                        @if($user->blocked_at)
                                            <i class="fas fa-unlock"></i> Mở khóa
                                        @else
                                            <i class="fas fa-ban"></i> Khóa
                                        @endif
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400 italic">Tài khoản của bạn</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            Không tìm thấy người dùng nào
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
