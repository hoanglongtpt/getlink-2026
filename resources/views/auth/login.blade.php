@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold">Đăng nhập</h1>

        @if(session('status'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" required placeholder="ví dụ: you@example.com" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <input id="password" name="password" type="password" required placeholder="Nhập mật khẩu" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>
            <div class="flex items-center justify-between text-sm text-slate-600">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Nhớ đăng nhập
                </label>
                <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-700">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">Đăng nhập</button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            <p>Hoặc tiếp tục với</p>
            <a href="{{ route('auth.google.redirect') }}" class="mt-3 inline-block rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:border-slate-300 hover:bg-slate-50 transition">Google</a>
        </div>

        <p class="mt-6 text-center text-sm text-slate-600">Bạn chưa có tài khoản? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700">Đăng ký</a></p>
    </div>
</div>
@endsection
