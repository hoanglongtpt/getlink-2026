@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold">Sign In</h1>

        @if($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required class="w-full rounded border-gray-300 p-2">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded border-gray-300 p-2">
            </div>
            <div class="flex items-center justify-between text-sm text-slate-600">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                    Remember me
                </label>
            </div>
            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 text-white">Log in</button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            <p>Or continue with</p>
            <a href="{{ route('auth.google.redirect') }}" class="mt-3 inline-block rounded border border-slate-200 px-4 py-2 text-slate-700">Google</a>
        </div>

        <p class="mt-6 text-center text-sm">Don't have an account? <a href="{{ route('register') }}" class="text-blue-600">Register</a></p>
    </div>
</div>
@endsection
