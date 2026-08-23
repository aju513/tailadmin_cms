@extends('layouts.public')
@section('content')
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}" class="hover:text-brand-600">Home</a><span class="mx-2">/</span>{{ $page->title }}</nav>
    @if($page->bannerMedia)
        <img src="{{ $page->bannerMedia->url() }}" alt="{{ $page->bannerMedia->alt_text ?: $page->title }}" class="mb-6 h-56 w-full rounded-2xl object-cover shadow-sm sm:h-72">
    @endif
    <article class="prose max-w-none rounded-2xl bg-white p-6 shadow-sm sm:p-10">
        <h1>{{ $page->title }}</h1>
        @if($page->summary)<div class="lead">{!! $page->summary !!}</div>@endif
        <div>{!! $page->body !!}</div>
        @if($page->children->isNotEmpty())<h2>Related pages</h2><ul>@foreach($page->children as $child)<li><a href="{{ route('public.page', ['path' => $child->path]) }}">{{ $child->title }}</a></li>@endforeach</ul>@endif
    </article>
@endsection
