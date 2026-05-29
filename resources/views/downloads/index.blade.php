@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Tải tài nguyên</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('downloads.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="link" class="block font-medium">Link tài nguyên</label>
            <input id="link" name="link" type="url" value="{{ old('link') }}" class="w-full rounded border-gray-300 p-2" required>
        </div>

        <div>
            <label for="ispre" class="block font-medium">Loại tài nguyên</label>
            <select id="ispre" name="ispre" class="w-full rounded border-gray-300 p-2" required>
                <option value="0"{{ old('ispre') === '0' ? ' selected' : '' }}>Normal</option>
                <option value="1"{{ old('ispre') === '1' ? ' selected' : '' }}>Premium</option>
            </select>
        </div>

        <button type="submit" class="rounded bg-blue-600 text-white px-4 py-2">Yêu cầu tải xuống</button>
    </form>
</div>
@endsection
