@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold">Register</h1>

        @if($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" required class="w-full rounded border-gray-300 p-2">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required class="w-full rounded border-gray-300 p-2">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded border-gray-300 p-2">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded border-gray-300 p-2">
            </div>
            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 text-white">Create account</button>
        </form>

        <p class="mt-6 text-center text-sm">Already have an account? <a href="{{ route('login') }}" class="text-blue-600">Sign in</a></p>
    </div>
</div>
@endsection
