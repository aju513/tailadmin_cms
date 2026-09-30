@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Page Details" />
<x-common.component-card title="{{ $page->title }}" desc="/{{ $page->path }}">
    <div class="space-y-4 text-sm">
        <p><strong>Page type:</strong> {{ $page->page_type?->label() ?? 'Standard Page' }}</p>
        @if($page->page_type === \App\Enums\PageType::Resource)
            <p><strong>Resource category:</strong> {{ $page->resourceCategory?->name ?? 'All categories' }}</p>
        @endif
        @if($page->page_type === \App\Enums\PageType::Notices)
            <p><strong>Notice type:</strong> {{ $page->notice_type?->label() ?? 'All types' }}</p>
        @endif
        <p><strong>Status:</strong> {{ ucfirst($page->status->value) }}</p>
        <p><strong>Summary:</strong> {{ $page->summary ?: '—' }}</p>
        <div><strong>Content:</strong><div class="prose mt-2 max-w-none">{!! $page->body !!}</div></div>
        @if($page->children->isNotEmpty())
            <div><strong>Child pages:</strong><ul class="mt-2 list-disc pl-5">@foreach($page->children as $child)<li>{{ $child->title }} ({{ $child->path }})</li>@endforeach</ul></div>
        @endif
    </div>
</x-common.component-card>
@endsection
