<section class="album-page" aria-label="Photo albums">
    <div class="container pb-12">
        <div class="page-title"><h1>{{ $heading }}</h1></div>
        @if(isset($page) && $page->summary)<div class="mb-6 text-text_color">{!! $safeHtml->clean($page->summary) !!}</div>@endif
        @include('front.components.catalogue-search', ['searchLabel' => 'Search photo albums'])
        <div class="album-list mt-8"><div class="grid grid-cols-12 gap-5">
            @forelse($items as $item)
                <div class="col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-3">
                    <a href="{{ route('public.gallery.show', $item->slug) }}" class="relative block album-list__item" aria-label="Open album: {{ $item->title }}">
                        <div class="album-list__item-image"><div class="placeholder__img-wrapper"><div class="w-full placeholder__img"><x-front.image :media="$item->coverMedia ?? $item->photos->first()?->media" :alt="$item->title" width="600" height="450" class="rounded-[5px]" /></div></div></div>
                        <div class="album-list__item-content absolute bottom-0 left-0 z-10 w-full rounded-b-[5px] bg-black/60 px-4 py-2 text-lg font-bold text-white"><span>{{ $item->title }}</span><span class="block text-sm font-medium">{{ $item->photos_count }} photos</span></div>
                    </a>
                </div>
            @empty
                <p class="col-span-12 py-8 text-text_color">{{ request()->filled('q') || request()->filled('search') ? 'No albums match your search.' : 'No galleries published yet.' }}</p>
            @endforelse
        </div></div>
        <div class="mt-6">{{ $items->links('front.components.pagination') }}</div>
        @if(isset($page) && $page->body)<article class="prose mt-8 max-w-none">{!! $safeHtml->clean($page->body) !!}</article>@endif
    </div>
</section>
