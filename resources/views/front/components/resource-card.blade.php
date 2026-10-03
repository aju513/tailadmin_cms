<article class="training-list__item h-full">
    <h3 class="training-list__title">
        <a href="{{ route('public.resources.show', $item->slug) }}">{{ $item->title }}</a>
    </h3>
    <a href="{{ route('public.resources.show', $item->slug) }}" class="btn-primary hav-icon training-list__link mt-auto! bg-white! text-[#164491]!">
        <span class="font-medium hover:underline!">View Details</span>
        <span class="btn-primary__icon icon-arrow-up-right bg-[#10336F]/80!" aria-hidden="true"></span>
    </a>
</article>
