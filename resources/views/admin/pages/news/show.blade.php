@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="News Details">
    <x-slot:actions>@can('news.edit')<a href="{{ route('admin.news.edit', $item) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">Edit news</a>@endcan</x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card :title="$item->title" :desc="'/news/'.$item->slug">
    <div class="space-y-5 text-sm text-gray-700 dark:text-gray-300">
        <p><strong>Status:</strong> {{ ucfirst($item->status->value) }} @if($item->featured) · Featured @endif</p>
        <p><strong>Published:</strong> {{ $item->published_at?->format('M d, Y') ?? '—' }}</p>
        @if($item->thumbnailMedia)<img src="{{ $item->thumbnailMedia->url() }}" alt="{{ $item->thumbnailMedia->alt_text ?: $item->title }}" class="h-48 w-auto rounded-xl object-cover">@endif
        @if($item->subtitle)<p class="text-lg font-medium">{{ $item->subtitle }}</p>@endif
        @if($item->excerpt)<div class="prose max-w-none">{!! $item->excerpt !!}</div>@endif
        <div class="prose max-w-none">{!! $item->body !!}</div>
        @if($item->status->value === 'published')<a href="{{ route('public.news.show', $item->slug) }}" target="_blank" rel="noopener" class="text-brand-600 underline">View public article</a>@endif
    </div>
</x-common.component-card>
@endsection
