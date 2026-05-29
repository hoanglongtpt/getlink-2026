<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GetLink 2026') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="border-b bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <div>
                <a href="{{ url('/') }}" class="text-lg font-bold">{{ config('app.name', 'GetLink 2026') }}</a>
            </div>
            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-slate-700">Tải tài nguyên</a>
                    <a href="{{ url('/admin') }}" class="text-slate-700">Admin</a>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-700">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="text-slate-700">Đăng nhập</a>
                    <a href="{{ url('/register') }}" class="text-slate-700">Đăng ký</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="min-h-screen">
        @yield('content')
    </main>
</body>
</html>
