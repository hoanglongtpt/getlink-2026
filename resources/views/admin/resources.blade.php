@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Cached Resources</h1>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-700">
                <tr>
                    <th class="px-4 py-3">Original Link</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Drive Link</th>
                    <th class="px-4 py-3">Downloads</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($resources as $resource)
                    <tr>
                        <td class="px-4 py-3 break-words">{{ \Illuminate\Support\Str::limit($resource->original_link, 80) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($resource->status) }}</td>
                        <td class="px-4 py-3">
                            @if($resource->google_drive_link)
                                <a href="{{ $resource->google_drive_link }}" class="text-blue-600" target="_blank">Open</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $resource->download_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $resources->links() }}</div>
</div>
@endsection
