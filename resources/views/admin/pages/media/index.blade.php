@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Media Library" />

<x-common.component-card title="Upload media" desc="Images and office documents can be reused across content.">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-3">
        @csrf
        <div class="md:col-span-3"><x-form.file-upload name="file" label="Media file" upload-profile="uploads.media" required /></div>
        <x-form.input name="title" label="Title" :value="old('title')" />
        <x-form.input name="alt_text" label="Alt text for images" :value="old('alt_text')" />
        <div class="self-end"><x-ui.button type="submit">Upload</x-ui.button></div>
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
