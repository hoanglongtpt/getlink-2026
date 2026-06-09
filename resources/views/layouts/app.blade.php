<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GetLink 2026') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">

    @auth
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <a href="{{ url('/') }}" class="text-xl font-bold tracking-wider flex items-center gap-2 text-purple-700">
                <i class="fas fa-cloud-download-alt"></i> {{ config('app.name', 'GetLink') }}
            </a>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->is('dashboard') || request()->is('/') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-purple-600' }} transition">
                <i class="fas fa-home w-5"></i> Bảng điều khiển
            </a>
            
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Providers</p>
            </div>
            
            <div class="space-y-1 max-h-60 overflow-y-auto pr-1" style="scrollbar-width: thin;">
                @if(isset($providers) && count($providers) > 0)
                    @foreach($providers as $provider)
                        <div class="flex items-center justify-between px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 group">
                            <div class="flex items-center gap-2 truncate">
                                <i class="fas fa-cube text-gray-400 group-hover:text-purple-500"></i>
                                <span class="truncate">{{ $provider->display_name }}</span>
                            </div>
                            <span class="text-xs font-medium bg-purple-100 text-purple-700 py-0.5 px-2 rounded-full whitespace-nowrap">{{ $provider->xu_cost }} Xu</span>
                        </div>
                    @endforeach
                @else
                    <p class="px-4 py-2 text-sm text-gray-400 italic">Chưa có provider nào</p>
                @endif
            </div>

            <div class="pt-4 pb-2 border-t border-gray-100 mt-2">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tài khoản</p>
            </div>
            
            <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('packages.index') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-purple-600' }} transition">
                <i class="fas fa-coins w-5"></i> Nạp Xu (Bảng Giá)
            </a>
        </nav>
    </aside>
    @endauth

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <header class="h-16 flex-shrink-0 bg-white shadow-sm flex items-center justify-between px-6 lg:px-8 z-10 text-gray-800">
            <div class="flex items-center gap-4">
                @auth
                <button class="md:hidden text-gray-600 hover:text-purple-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                @else
                <a href="{{ url('/') }}" class="text-xl font-bold tracking-wider flex items-center gap-2 text-purple-700">
                    <i class="fas fa-cloud-download-alt"></i> {{ config('app.name', 'GetLink') }}
                </a>
                @endauth
                <h2 class="text-lg font-semibold hidden sm:block text-gray-700">
                    @yield('header_title', 'Dashboard')
                </h2>
            </div>
            
            <div class="flex items-center gap-4 relative">
                @auth
                <a href="{{ route('packages.index') }}" class="flex items-center gap-2 bg-purple-50 px-3 py-1.5 rounded-full border border-purple-100 hover:bg-purple-100 transition">
                    <i class="fas fa-plus-circle text-purple-600"></i>
                    <span class="font-bold xu-balance-display text-purple-800">{{ Auth::user()->xu_balance }} Xu <span class="text-orange-500 ml-1 text-xs">+{{ Auth::user()->bonus_xu }}</span></span>
                </a>
                
                <!-- User Menu Toggle -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white flex items-center justify-center font-bold shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 border border-gray-100 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ Auth::user()->email }}</p>
                        </div>
                        
                        <div class="py-1">
                            <a href="{{ url('/profile') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition">
                                <i class="fas fa-user w-4 text-center"></i> Tài khoản cá nhân
                            </a>
                            <a href="{{ url('/profile') }}#history" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition">
                                <i class="fas fa-history w-4 text-center"></i> Lịch sử tải
                            </a>
                            @if(Auth::user() && Auth::user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition">
                                <i class="fas fa-cog w-4 text-center"></i> Quản trị hệ thống
                            </a>
                            @endif
                        </div>
                        
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ url('/logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <nav class="flex items-center gap-4 text-sm font-medium">
                    <a href="{{ url('/login') }}" class="text-gray-600 hover:text-purple-600">Đăng nhập</a>
                    <a href="{{ url('/register') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition shadow-sm">Đăng ký</a>
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