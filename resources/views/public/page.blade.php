@extends('layouts.public')
@section('content')
    @if(config('settings.nepali'))
    <nav aria-label="Page language" class="mb-5 flex justify-end gap-2 text-sm">
        <a href="{{ route('public.page', ['path' => $page->path, 'lang' => 'en']) }}" @if(app()->getLocale() === 'en') aria-current="page" @endif class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 {{ app()->getLocale() === 'en' ? 'border-brand-500 text-brand-600' : 'border-gray-300 text-gray-600' }}"><img src="{{ asset('images/flags/en.svg') }}" alt="" class="h-4 w-6 object-contain"> English</a>
        <a href="{{ route('public.page', ['path' => $page->path, 'lang' => 'ne']) }}" @if(app()->getLocale() === 'ne') aria-current="page" @endif class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 {{ app()->getLocale() === 'ne' ? 'border-brand-500 text-brand-600' : 'border-gray-300 text-gray-600' }}"><img src="{{ asset('images/flags/np.svg') }}" alt="" class="h-4 w-5 object-contain"> नेपाली</a>
    </nav>
    @endif
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}" class="hover:text-brand-600">Home</a><span class="mx-2">/</span>{{ $page->title }}</nav>
    @if($page->bannerMedia)
        <img src="{{ $page->bannerMedia->url() }}" alt="{{ $page->bannerMedia->alt_text ?: $page->title }}" class="mb-6 h-56 w-full rounded-2xl object-cover shadow-sm sm:h-72">
    @endif
    <article class="prose max-w-none rounded-2xl bg-white p-6 shadow-sm sm:p-10">
        <h1>{{ $page->title }}</h1>
        @if($page->summary)<div class="lead">{!! $page->summary !!}</div>@endif
        <div>{!! $page->body !!}</div>
        @if($page->children->isNotEmpty())<h2>Related pages</h2><ul>@foreach($page->children as $child)<li><a href="{{ route('public.page', ['path' => $child->path, 'lang' => app()->getLocale()]) }}">{{ $child->title }}</a></li>@endforeach</ul>@endif
    </article>
@endsection
