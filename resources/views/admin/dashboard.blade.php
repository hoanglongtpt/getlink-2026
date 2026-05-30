@extends('layouts.admin')

@section('header_title', 'Tổng quan (Dashboard)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Box 1 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Tổng người dùng</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalUsers) }}</p>
        </div>
    </div>

    <!-- Stat Box 2 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-2xl">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Giao dịch nạp</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalTransactions) }}</p>
        </div>
    </div>

    <!-- Stat Box 3 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-2xl">
            <i class="fas fa-download"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Lượt tải tài nguyên</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalDownloads) }}</p>
        </div>
    </div>

    <!-- Stat Box 4 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-2xl">
            <i class="fas fa-database"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Tài nguyên đã Cache</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalResources) }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Chào mừng đến với Admin Panel</h3>
    <p class="text-gray-500">Sử dụng menu bên trái để điều hướng và quản lý hệ thống GetLink.</p>
</div>
@endsection
