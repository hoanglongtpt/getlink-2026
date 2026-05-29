@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Settings</h1>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="rounded bg-white p-6 shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="download_fee" class="block font-medium">Download Fee (Xu)</label>
                <input id="download_fee" name="download_fee" type="number" value="{{ old('download_fee', $downloadFee) }}" class="w-full rounded border-gray-300 p-2" required>
            </div>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Save</button>
        </form>
    </div>

    <div class="mt-8 rounded bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Upload Google Service Account JSON</h2>
        <form action="{{ route('admin.google.upload') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <input type="file" name="google_service_account" accept="application/json" required>
            </div>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Upload Key</button>
        </form>
    </div>
</div>
@endsection
