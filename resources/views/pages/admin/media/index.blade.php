@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Media Library" />

<x-common.component-card title="Upload media" desc="Images and office documents can be reused across content.">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-3">
        @csrf
        <input type="file" name="file" required class="rounded-lg border border-gray-300 px-3 py-2">
        <input name="title" placeholder="Title" class="rounded-lg border border-gray-300 px-3 py-2">
        <input name="alt_text" placeholder="Alt text for images" class="rounded-lg border border-gray-300 px-3 py-2">
        <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">Upload</button>
    </form>
</x-common.component-card>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @forelse($media as $asset)
        <x-common.component-card :title="$asset->title ?: $asset->original_name">
            <p class="text-xs text-gray-500">{{ $asset->mime_type }} · {{ number_format($asset->size / 1024, 1) }} KB</p>
            @if(str_starts_with($asset->mime_type, 'image/'))
                <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->title }}" class="mt-3 h-32 w-full rounded object-cover">
            @endif
            @can('media.delete')
                <form method="POST" action="{{ route('admin.media.destroy', $asset) }}" class="mt-3" onsubmit="return confirm('Delete this file?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg px-2 py-1 text-xs text-error-600 transition hover:bg-error-50 hover:text-error-700 dark:hover:bg-error-500/10">Delete</button>
                </form>
            @endcan
        </x-common.component-card>
    @empty
        <p class="text-sm text-gray-500">No media uploaded.</p>
    @endforelse
</div>
@endsection
