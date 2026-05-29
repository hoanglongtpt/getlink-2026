@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded border p-4 shadow-sm">
            <h2 class="text-xl font-semibold">Users</h2>
            <p class="text-3xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="rounded border p-4 shadow-sm">
            <h2 class="text-xl font-semibold">Transactions</h2>
            <p class="text-3xl font-bold">{{ $totalTransactions }}</p>
        </div>
        <div class="rounded border p-4 shadow-sm">
            <h2 class="text-xl font-semibold">Downloads</h2>
            <p class="text-3xl font-bold">{{ $totalDownloads }}</p>
        </div>
        <div class="rounded border p-4 shadow-sm">
            <h2 class="text-xl font-semibold">Resources</h2>
            <p class="text-3xl font-bold">{{ $totalResources }}</p>
        </div>
    </div>
</div>
@endsection
