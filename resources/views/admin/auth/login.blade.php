<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ config('app.name', 'GetLink') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white flex items-center justify-center gap-2">
                <i class="fas fa-shield-alt text-purple-500"></i> Admin Portal
            </h1>
            <p class="text-gray-400 text-sm mt-2">Vui lòng đăng nhập để vào khu vực quản trị</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-500"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-500"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900">
                    <label for="remember" class="ml-2 block text-sm text-gray-400">Remember me</label>
                </div>
                <a href="{{ url('/') }}" class="text-sm text-purple-400 hover:text-purple-300">Về trang chủ</a>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-purple-500 transition">
                Đăng nhập Admin
            </button>
        </form>
    </div>
</body>
</html>