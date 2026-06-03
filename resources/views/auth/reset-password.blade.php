@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold">Đặt lại mật khẩu</h1>

        @if($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="Nhập email của bạn" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu mới</label>
                <input id="password" name="password" type="password" required placeholder="Tối thiểu 8 ký tự" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Nhập lại mật khẩu" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">Cập nhật mật khẩu</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">Quay lại <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700">đăng nhập</a></p>
    </div>
</div>
@endsection
