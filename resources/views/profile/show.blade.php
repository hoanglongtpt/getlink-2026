@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold">Profile</h1>
            <p class="mt-4"><strong>Name:</strong> {{ $user->name }}</p>
            <p class="mt-2"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="mt-2"><strong>Balance:</strong> {{ $user->xu_balance }} Xu</p>
            <p class="mt-2"><strong>Member since:</strong> {{ $user->created_at->format('Y-m-d') }}</p>
        </div>
        <div class="lg:col-span-2 rounded bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold">Latest downloads</h2>
            <table class="mt-4 w-full text-left text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Link</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr class="border-b">
                            <td class="py-2">{{ \Illuminate\Support\Str::limit($history->original_link, 60) }}</td>
                            <td class="py-2">{{ ucfirst($history->status) }}</td>
                            <td class="py-2">{{ $history->xu_cost }} Xu</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-slate-500">No records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
