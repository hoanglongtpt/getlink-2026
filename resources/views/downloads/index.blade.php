@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Download Resource</h1>
            <p class="text-sm text-slate-600">Your current balance: <strong>{{ Auth::user()->xu_balance }} Xu</strong></p>
        </div>
        <div class="rounded bg-white px-4 py-3 shadow-sm">
            <p class="text-sm text-slate-700">Download fee: <strong>{{ $downloadFee }} Xu</strong> per request</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4 text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('downloads.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="link" class="block font-medium">Resource link</label>
                <input id="link" name="link" type="url" value="{{ old('link') }}" class="w-full rounded border-gray-300 p-2" required>
            </div>
            <div>
                <label for="ispre" class="block font-medium">Resource type</label>
                <select id="ispre" name="ispre" class="w-full rounded border-gray-300 p-2" required>
                    <option value="0"{{ old('ispre') === '0' ? ' selected' : '' }}>Normal</option>
                    <option value="1"{{ old('ispre') === '1' ? ' selected' : '' }}>Premium</option>
                </select>
            </div>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Submit download</button>
        </form>
    </div>

    <div class="mt-8 rounded bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Recent download history</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Link</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Cost</th>
                        <th class="px-4 py-3">Direct link</th>
                        <th class="px-4 py-3">Requested at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($histories as $history)
                        <tr>
                            <td class="px-4 py-3 break-words">{{ \Illuminate\Support\Str::limit($history->original_link, 60) }}</td>
                            <td class="px-4 py-3">{{ ucfirst($history->status) }}</td>
                            <td class="px-4 py-3">{{ $history->xu_cost }} Xu</td>
                            <td class="px-4 py-3">
                                @if($history->direct_download_link)
                                    <a href="{{ $history->direct_download_link }}" class="text-blue-600" target="_blank">Download</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No downloads yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
