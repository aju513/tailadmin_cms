<div class="gallery-page" role="main">
    <div class="container">
        <div class="page-title">
            <h1>
                {{ $item->title }}
            </h1>
        </div>
        <div class="gallery-list">
            <div class="grid grid-cols-12 gap-3.75 lg:gap-5">
@foreach($item->photos as $photo)@if($photo->media)
<div class="col-span-6 md:col-span-4 lg:col-span-3">
                    <div class="gallery-list__item ">
                        <a href="{{ $photo->media?->url() }}" data-caption="{{ $photo->caption }}"
                            data-fancybox="gallery">
                            <div class="placeholder__img-wrapper">
                                <div class="w-full placeholder__img">
                                    <x-front.image :media="$photo->media" :alt="$photo->caption ?: $item->title" width="600" height="450" class="rounded-[5px]" :priority="$loop->first" />
                                </div>
                            </div>
                            <span class="zoom-icon">
                                <span class="text-2xl text-white icon-magnify-glass"></span>
                            </span>
                        </a>
                    </div>
                </div>
@endif
@endforeach
</div>
        </div>
    </div>
</div>
