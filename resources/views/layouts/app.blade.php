<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GetLink 2026') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">

    @auth
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-gradient-to-b from-purple-800 to-purple-900 text-white flex flex-col transition-all duration-300 hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-purple-700/50">
            <a href="{{ url('/') }}" class="text-xl font-bold tracking-wider flex items-center gap-2">
                <i class="fas fa-cloud-download-alt"></i> {{ config('app.name', 'GetLink') }}
            </a>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-700/50 text-white font-medium hover:bg-purple-700 transition">
                <i class="fas fa-home w-5"></i> Dashboard
            </a>
            <a href="{{ url('/profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-purple-200 hover:bg-purple-700 hover:text-white transition">
                <i class="fas fa-user w-5"></i> Cá Nhân
            </a>
            @if(Auth::user() && Auth::user()->isAdmin())
            <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-purple-200 hover:bg-purple-700 hover:text-white transition">
                <i class="fas fa-cog w-5"></i> Admin Panel
            </a>
            @endif
        </nav>
        <div class="p-4 border-t border-purple-700/50">
            <div class="flex items-center gap-3 px-4 py-2">
                <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-purple-300 xu-balance-display">{{ Auth::user()->xu_balance }} Xu</p>
                </div>
            </div>
            <form method="POST" action="{{ url('/logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-purple-200 hover:bg-purple-700 hover:text-white transition">
                    <i class="fas fa-sign-out-alt w-5"></i> Đăng xuất
                </button>
            </form>
        </div>
    </aside>
    @endauth

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <header class="h-16 flex-shrink-0 bg-gradient-to-r from-purple-700 to-purple-800 shadow flex items-center justify-between px-6 lg:px-8 z-10 text-white">
            <div class="flex items-center gap-4">
                @auth
                <button class="md:hidden text-white hover:text-purple-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                @else
                <a href="{{ url('/') }}" class="text-xl font-bold tracking-wider flex items-center gap-2">
                    <i class="fas fa-cloud-download-alt"></i> {{ config('app.name', 'GetLink') }}
                </a>
                @endauth
                <h2 class="text-lg font-semibold hidden sm:block">
                    @yield('header_title', 'Dashboard')
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                @auth
                <div class="flex items-center gap-2 bg-purple-900/50 px-3 py-1.5 rounded-full border border-purple-500/30">
                    <i class="fas fa-coins text-yellow-400"></i>
                    <span class="font-bold xu-balance-display">{{ Auth::user()->xu_balance }} Xu</span>
                </div>
                @else
                <nav class="flex items-center gap-4 text-sm font-medium">
                    <a href="{{ url('/login') }}" class="hover:text-purple-200">Đăng nhập</a>
                    <a href="{{ url('/register') }}" class="bg-white text-purple-800 px-4 py-2 rounded-lg hover:bg-gray-100 transition">Đăng ký</a>
                </nav>
                @endauth
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>