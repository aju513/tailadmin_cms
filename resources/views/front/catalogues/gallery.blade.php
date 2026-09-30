<div class="album-page" role="main">
    <div class="container">
        <div class="page-title">
            <h1>
                {{ $heading }}
            </h1>
        </div>

        <div class="album-list">
            <div class="grid grid-cols-12 gap-5">
@forelse($items as $item)
<div class="col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-3">
                    <div class="relative album-list__item">
                        <div class="album-list__item-image">
                            <a href="{{ route('public.gallery.show',$item->slug) }}">
                                <div class="placeholder__img-wrapper">
                                    <div class="w-full placeholder__img">
                                        <x-front.image :media="$item->photos->first()?->media" :alt="$item->title" width="600" height="450" class="rounded-[5px]" />
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="album-list__item-content absolute bottom-0 left-0 z-10 w-full rounded-b-[5px] bg-black/60 px-4 py-2 text-lg font-bold text-white">
                            <a href="{{ route('public.gallery.show',$item->slug) }}" class="text-white">
                                {{ $item->title }}
                            </a>
                            <span class="block text-sm font-medium trip-count">
                                {{ $item->photos_count }} photos
                            </span>
                        </div>
                    </div>
                </div>
@empty<p class="col-span-12 py-8 text-text_color">No galleries published yet.</p>@endforelse
</div>
        </div>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
