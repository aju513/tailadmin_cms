@extends('front.layouts.app')
@inject('urls','App\Services\Frontend\SeoService')
@section('content')
@include('front.partials.breadcrumbs')
<section class="search-page" aria-label="Website search">
    <div class="container pb-12">
        <div class="page-title"><h1>{{ $term ? 'You searched for '.$term : 'Search' }}</h1></div>
        @include('front.components.catalogue-search', ['searchLabel' => 'Search the website'])
        @foreach($results as $resultKind => $records)
            @if($records->total())
                <section class="mt-8" aria-label="{{ ucfirst($resultKind) }} results">
                    <h2 class="mb-5 text-xl font-bold text-primary">{{ ['pages'=>'Pages','news'=>'News','notices'=>'Notices','resources'=>'Resources','halls'=>'Halls','gallery'=>'Photo albums','videos'=>'Videos','team'=>'Team members'][$resultKind] }} ({{ $records->total() }})</h2>
                    <div class="grid grid-cols-12 gap-5 package-list">
                        @foreach($records as $item)
                            @php($title = $item->title ?? $item->name)
                            <article class="col-span-12 sm:col-span-6 lg:col-span-4 package-list__item">
                                <div class="package-list__item-image"><a href="{{ $urls->recordPath($resultKind, $item) }}"><x-front.image :media="$item->thumbnailMedia ?? $item->bannerMedia ?? $item->coverMedia ?? $item->photoMedia ?? $item->photos?->first()?->media" :alt="$title" width="600" height="450" /></a></div>
                                <div class="package-list__item-content">
                                    <h3 class="package-list__item-title"><a href="{{ $urls->recordPath($resultKind, $item) }}">{{ $title }}</a></h3>
                                    <p class="package-list__item-meta text-sm text-text_color">{{ Str::limit(strip_tags($item->summary ?? $item->description ?? $item->bio ?? ''), 160) }}</p>
                                    <div class="package-list__item-bottom"><a href="{{ $urls->recordPath($resultKind, $item) }}" class="text-primary">View Details</a></div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-5">{{ $records->links('front.components.pagination') }}</div>
                </section>
            @endif
        @endforeach
        @if(! $term)<p class="py-8 text-text_color">Enter a term to search published website content.</p>@elseif(collect($results)->every(fn ($records) => $records->total() === 0))<p class="py-8 text-text_color">No results found.</p>@endif
    </div>
</section>
@endsection
