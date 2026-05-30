<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - {{ config('app.name', 'GetLink') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Admin Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-gray-900 text-white flex flex-col transition-all duration-300 hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-gray-800 bg-gray-950">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold tracking-wider flex items-center gap-2 text-purple-400">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Quản lý</p>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <i class="fas fa-chart-pie w-5"></i> Tổng quan
            </a>
            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <i class="fas fa-users w-5"></i> Người dùng
            </a>
            <a href="{{ route('admin.transactions') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.transactions') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <i class="fas fa-money-bill-wave w-5"></i> Giao dịch nạp
            </a>
            <a href="{{ route('admin.resources') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.resources') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <i class="fas fa-box-open w-5"></i> Tài nguyên Cache
            </a>
            
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 pt-4 border-t border-gray-800">Cấu hình</p>
            
            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.settings') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <i class="fas fa-cogs w-5"></i> Cài đặt hệ thống
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center gap-3 px-4 py-2">
                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center font-bold">
                    {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-medium truncate">{{ Auth::guard('admin')->user()->name }}</p>
                    <p class="text-xs text-green-400">Online</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition">
                    <i class="fas fa-sign-out-alt w-5"></i> Đăng xuất Admin
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <header class="h-16 flex-shrink-0 bg-white shadow-sm flex items-center justify-between px-6 lg:px-8 z-10 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-800 hidden sm:block">
                    @yield('header_title', 'Dashboard')
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" target="_blank" class="text-sm font-medium text-gray-600 hover:text-purple-600 flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-purple-50 transition">
                    <i class="fas fa-external-link-alt"></i> Xem Website
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                @if(session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start gap-3 shadow-sm">
                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                        <div class="text-green-800 font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3 shadow-sm">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                        <div class="text-red-800 font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>