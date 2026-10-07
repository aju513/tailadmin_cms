<article class="training-list__item h-full">
    <h3 class="training-list__title">
        <a href="{{ route('public.resources.show', $item->slug) }}">{{ $item->title }}</a>
    </h3>
    <a href="{{ route('public.resources.show', $item->slug) }}" class="btn-venue btn-venue--compact mt-auto!">
        <span class="btn-venue__text">View Details</span>
        <span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span>
    </a>
</article>
