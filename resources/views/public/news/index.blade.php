@extends('layouts.public')

@section('content')
<nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}" class="hover:text-brand-600">Home</a><span class="mx-2">/</span><a href="{{ route('public.news.index') }}" class="hover:text-brand-600">News</a>@if($heading !== 'News')<span class="mx-2">/</span>{{ $heading }}@endif</nav>
<div class="mb-8">
    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-brand-600">Latest updates</p>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $heading }}</h1>
    @if($author?->bio)<div class="prose mt-4 max-w-3xl text-gray-600">{!! $author->bio !!}</div>@endif
</div>

@if($featured)
<section aria-label="Featured news" class="mb-10 overflow-hidden rounded-2xl bg-white shadow-sm lg:grid lg:grid-cols-2">
    <a href="{{ route('public.news.show', $featured->slug) }}" class="block">@if($featured->thumbnailMedia)<img src="{{ $featured->thumbnailMedia->url() }}" alt="{{ $featured->thumbnailMedia->alt_text ?: $featured->title }}" class="h-64 w-full object-cover lg:h-full">@else<div class="flex h-64 items-center justify-center bg-gray-100 text-gray-400">Featured news</div>@endif</a>
    <div class="flex flex-col justify-center p-7 sm:p-10">
        <span class="mb-4 w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-brand-600">Featured news</span>
        <h2 class="text-2xl font-bold text-gray-900"><a href="{{ route('public.news.show', $featured->slug) }}" class="hover:text-brand-600">{{ $featured->title }}</a></h2>
        <div class="mt-3 text-sm text-gray-500">{{ $featured->published_at?->format('F d, Y') }} @if($featured->category) · {{ $featured->category->name }} @endif @if($featured->author) · By {{ $featured->author->name }} @endif</div>
        @if($featured->excerpt)<p class="mt-5 leading-7 text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($featured->excerpt), 240) }}</p>@endif
        <a href="{{ route('public.news.show', $featured->slug) }}" class="mt-5 font-medium text-brand-600 hover:underline">Continue reading →</a>
    </div>
</section>
@endif

<div class="mb-8 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <label class="text-sm text-gray-600">Browse category
        <select aria-label="Browse news category" onchange="if (this.value) window.location.href = this.value" class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm sm:w-64">
            <option value="{{ route('public.news.index') }}">All categories</option>
            @foreach($categories as $category)<option value="{{ route('public.news.category', $category->slug) }}" @selected(request()->routeIs('public.news.category') && request()->route('slug') === $category->slug)>{{ $category->name }}</option>@endforeach
        </select>
    </label>
    <form method="GET" action="{{ url()->current() }}" class="flex gap-2"><label for="news-search" class="sr-only">Search news</label><input id="news-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search news" class="h-11 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-4 text-sm"><button class="rounded-lg bg-brand-500 px-5 text-sm font-medium text-white">Search</button></form>
</div>

<section aria-label="News articles">
    <h2 class="mb-5 text-2xl font-bold text-gray-900">Latest news</h2>
    @if($items->isEmpty())
        <div class="rounded-xl bg-brand-50 p-8 text-center text-sm text-gray-600">No news articles found.</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($items as $item)@include('public.news._card', ['item' => $item])@endforeach</div>
        <div class="mt-8">{{ $items->links() }}</div>
    @endif
</section>
@endsection
