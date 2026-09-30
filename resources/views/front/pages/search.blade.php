@extends('front.layouts.app')
@inject('urls','App\Services\Frontend\SeoService')
@section('content')
@include('front.partials.breadcrumbs')
<div class="search-page" role="main">
    <div class="container">
        <div class="flex flex-wrap gap-y-2 items-center justify-between">
            <div class="mb-0 page-title"><h1>{{ $term ? 'You searched for '.$term : 'Search' }}</h1></div>
            <form class="search-list__sort flex flex-col gap-0 max-md:mt-3 max-md:w-full md:flex-row md:items-center md:gap-3 gap-y-2" method="GET" action="{{ route('public.search') }}">
                <div class="relative blog-list__sort-search md:w-67.5 max-sm:w-full">
                    <label class="sr-only" for="search-query">Search the website</label>
                    <input id="search-query" type="search" name="q" value="{{ $term }}" maxlength="100" placeholder="Search" class="border border-primary/20 rounded-md w-full pl-3.5 pr-12.5 py-3.75 text-[15px] leading-6 text-text_color placeholder:text-text_color">
                    <button type="submit" class="absolute right-3 top-3.5" aria-label="Search"><span class="text-2xl icon-search text-secondary"></span></button>
                </div>
            </form>
        </div>
        <div class="mt-7 search-page__list package-list">
            <div class="grid grid-cols-12 gap-5">
                @foreach($results as $resultKind=>$records)@foreach($records as $item)
                    <div class="col-span-12 sm:col-span-6 lg:col-span-4">
                        <div class="package-list__item">
                            <div class="top-badge">{{ ucfirst($resultKind) }}</div>
                            <div class="package-list__item-image"><div class="placeholder__img-wrapper"><div class="placeholder__img"><a href="{{ $urls->recordPath($resultKind,$item) }}"><x-front.image :media="$item->thumbnailMedia ?? $item->bannerMedia ?? $item->coverMedia ?? $item->photos?->first()?->media" :alt="$item->title" width="600" height="450" /></a></div></div></div>
                            <div class="package-list__item-content">
                                <h3 class="package-list__item-title"><a href="{{ $urls->recordPath($resultKind,$item) }}">{{ $item->title }}</a></h3>
                                <div class="package-list__item-meta"><span class="text-xs text-text_color">{{ Str::limit(strip_tags($item->excerpt ?? $item->summary ?? $item->description ?? ''),160) }}</span></div>
                                <div class="package-list__item-bottom"><div class="package-list__item-link"><a href="{{ $urls->recordPath($resultKind,$item) }}">View Details</a></div></div>
                            </div>
                        </div>
                    </div>
                @endforeach @endforeach
                @if(!$term)<p class="col-span-12 py-8 text-text_color">Enter a term to search published website content.</p>@elseif(collect($results)->every(fn($records)=>$records->isEmpty()))<p class="col-span-12 py-8 text-text_color">No results found.</p>@endif
            </div>
        </div>
    </div>
</div>
@endsection
