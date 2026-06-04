@extends('layouts.admin')

@section('header_title', 'Sửa thông tin người dùng')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-edit text-blue-600"></i> Sửa thông tin: {{ $user->name }}
            </h2>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 font-semibold mb-2">Có lỗi xảy ra:</p>
                    <ul class="text-red-600 text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Tên người dùng
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <!-- XU Balance -->
                <div>
                    <label for="xu_balance" class="block text-sm font-semibold text-gray-700 mb-2">
                        Số dư Xu (chính)
                    </label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="number" 
                            name="xu_balance" 
                            id="xu_balance"
                            value="{{ old('xu_balance', $user->xu_balance) }}"
                            min="0"
                            class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        <span class="text-yellow-600 font-semibold">Xu</span>
                    </div>
                </div>

                <!-- Bonus XU -->
                <div>
                    <label for="bonus_xu" class="block text-sm font-semibold text-gray-700 mb-2">
                        Xu Thưởng (bonus)
                    </label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="number" 
                            name="bonus_xu" 
                            id="bonus_xu"
                            value="{{ old('bonus_xu', $user->bonus_xu ?? 0) }}"
                            min="0"
                            class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        <span class="text-pink-600 font-semibold">Xu</span>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <strong>Lưu ý:</strong> Khi người dùng tải tài nguyên, hệ thống sẽ ưu tiên sử dụng Xu Thưởng (bonus) trước, sau đó dùng Xu chính (balance).
                    </p>
                </div>

                <!-- Current Info -->
                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Tổng Xu (chính + thưởng)</p>
                        <p class="text-xl font-bold text-purple-600">
                            {{ number_format(($user->xu_balance ?? 0) + ($user->bonus_xu ?? 0)) }} Xu
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Ngày tạo tài khoản</p>
                        <p class="text-sm text-gray-700">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                    <a 
                        href="{{ route('admin.users') }}"
                        class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold text-center"
                    >
                        <i class="fas fa-times mr-2"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
