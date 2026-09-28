@extends('layouts.public')

@section('content')
<nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}" class="hover:text-brand-600">Home</a><span class="mx-2">/</span><a href="{{ route('public.news.index') }}" class="hover:text-brand-600">News</a>@if($item->category)<span class="mx-2">/</span><a href="{{ route('public.news.category', $item->category->slug) }}" class="hover:text-brand-600">{{ $item->category->name }}</a>@endif</nav>
<div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_18rem]">
    <article class="min-w-0">
        <header class="mb-7">
            <h1 class="text-3xl font-bold leading-tight text-gray-900 sm:text-4xl">{{ $item->title }}</h1>
            @if($item->subtitle)<p class="mt-3 text-xl text-gray-600">{{ $item->subtitle }}</p>@endif
            <div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-gray-500"><time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('F d, Y') }}</time>@if($item->author)<span>·</span><a href="{{ route('public.news.author', $item->author->slug) }}" class="font-medium text-brand-600 hover:underline">{{ $item->author->name }}</a>@endif</div>
        </header>
        @if($item->bannerMedia)<img src="{{ $item->bannerMedia->url() }}" alt="{{ $item->bannerMedia->alt_text ?: $item->title }}" class="mb-8 max-h-[30rem] w-full rounded-2xl object-cover">@elseif($item->thumbnailMedia)<img src="{{ $item->thumbnailMedia->url() }}" alt="{{ $item->thumbnailMedia->alt_text ?: $item->title }}" class="mb-8 max-h-[30rem] w-full rounded-2xl object-cover">@endif
        @if($item->excerpt)<div class="prose mb-6 max-w-none text-lg text-gray-700">{!! $item->excerpt !!}</div>@endif
        <div class="prose max-w-none rounded-2xl bg-white p-6 shadow-sm sm:p-9">{!! $item->body !!}</div>
        @if($item->tags->isNotEmpty())<div class="mt-7 flex flex-wrap gap-2" aria-label="Article tags">@foreach($item->tags as $tag)<a href="{{ route('public.news.tag', $tag->slug) }}" class="rounded-full border border-brand-200 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-50">{{ $tag->name }}</a>@endforeach</div>@endif
    </article>
    <aside class="space-y-7">
        @if($item->author)<div class="rounded-2xl bg-white p-5 shadow-sm"><h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">About the author</h2><a href="{{ route('public.news.author', $item->author->slug) }}" class="font-semibold text-brand-600 hover:underline">{{ $item->author->name }}</a>@if($item->author->bio)<p class="mt-2 text-sm leading-6 text-gray-600">{{ strip_tags($item->author->bio) }}</p>@endif</div>@endif
        @if($related->isNotEmpty())<div class="rounded-2xl bg-white p-5 shadow-sm"><h2 class="mb-4 text-lg font-semibold text-gray-900">Latest news</h2><ul class="divide-y divide-gray-100">@foreach($related as $other)<li class="py-3 first:pt-0"><a href="{{ route('public.news.show', $other->slug) }}" class="font-medium text-gray-800 hover:text-brand-600">{{ $other->title }}</a><div class="mt-1 text-xs text-gray-500">{{ $other->published_at?->format('M d, Y') }}</div></li>@endforeach</ul></div>@endif
    </aside>
</div>
@endsection
