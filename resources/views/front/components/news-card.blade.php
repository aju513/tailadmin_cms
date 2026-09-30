<article class="homepage__news-card {{ $cardClass }}">
    <a
        class="homepage__news-image"
        href="{{ route('public.news.show',$item->slug) }}"
        aria-label="Read {{ $item->title }}">
        <x-front.image :media="$item->thumbnailMedia ?? $item->bannerMedia" :alt="$item->title" width="600" height="400" />
    </a>
    <div class="homepage__news-meta">
        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('d M, Y') }}</time>
        <span aria-hidden="true">|</span>
        <span>{{ $item->category?->name ?? $settings['address'] }}</span>
    </div>
    <h3 class="homepage__news-title">
        <a class="homepage__card-title-link text-[20px]!" href="{{ route('public.news.show',$item->slug) }}">
            {{ $item->title }}
        </a>
    </h3>
</article>
