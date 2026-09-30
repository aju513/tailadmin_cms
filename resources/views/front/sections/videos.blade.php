<section class="homepage__video-section scroll-wrap common-box pb-0" aria-labelledby="homepage-video-title">
    <div class="container-fluid">
        <div class="container max-md:!px-0 hav-title-btn">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 id="homepage-video-title" class="section-title homepage__section-title">Featured Videos</h2>
                <div class="section-title-btn">
                    <a href="{{ route('public.videos.index') }}" class="btn-outline-secondary hav-icon group shrink-0 whitespace-nowrap">
                        See All Videos
                        <span class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
            <div class="homepage__video-grid grid grid-cols-12">
@foreach($videos as $video)
@if($embeds->url($video->video_url))
<article class="homepage__moments-item col-span-6">
                    <div class="homepage__moments-item-image">
                        <div class="placeholder__img-wrapper">
                            <div class="placeholder__img">
                                <x-front.image :media="$video->coverMedia" :alt="$video->title" width="600" height="350" />
                            </div>
                        </div>
                        <div class="play-btn">
                            <a href="{{ $video->video_url }}" data-fancybox="homepage-video" aria-label="Play {{ $video->title }}">
                                <span class="flex items-center justify-center w-20 h-20 rounded-full circular-animate">
                                    <img class="homepage__video-play-icon" src="/front/images/svg/play.svg" alt="" width="32" height="32" loading="lazy" />
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="homepage__moments-item-content">
                        <h3 class="homepage__moments-item-title">
                            <a class="homepage__card-title-link" href="{{ $video->video_url }}" data-fancybox="homepage-video">{{ $video->title }}</a>
                        </h3>
                        <p class="homepage__moments-item-meta">{{ Str::limit(strip_tags($video->description), 140) }}</p>
                    </div>
                </article>
@endif
@endforeach
</div>
        </div>
    </div>
</section>
