@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Member Management</h1>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-700">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Balance (Xu)</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->xu_balance }}</td>
                        <td class="px-4 py-3">{{ $user->role }}</td>
                        <td class="px-4 py-3">
                            {{ $user->blocked_at ? 'Blocked' : 'Active' }}
                            @if($user->blocked_at)
                                <span class="ml-2 rounded bg-red-100 px-2 py-1 text-xs text-red-700">Blocked</span>
                            @else
                                <span class="ml-2 rounded bg-green-100 px-2 py-1 text-xs text-green-700">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded bg-slate-800 px-3 py-1 text-sm text-white hover:bg-slate-700">
                                    {{ $user->blocked_at ? 'Unblock' : 'Block' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
