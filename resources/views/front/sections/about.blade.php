<div class="homepage__about common-box pb-0">
    <div class="container-fluid">
        <div class="container ">
            <div class="grid grid-cols-12 gap-5 items-start">
                <div class="col-span-12 lg:col-span-7">
                    <div class="welcome-content">

                        <div class="section-title-wrap">
                            @if(!$homepageContent || filled($homepageContent->subtitle))
                                <div class="section-title-sm text-white! mb-1.5!">{{ $homepageContent?->subtitle ?? 'Our About Us' }}</div>
                            @endif

                            <h1 class="section-title text-white!">
                                {{ $homepageContent?->title ?? ($settings['about_title'] ?: $settings['site_name']) }}
                            </h1>
                        </div>

                        {!! preg_replace('/<p>/', '<p class="text-white/80!">', $safeHtml->clean($homepageContent?->body ?? $settings['about_description'])) !!}
<a href="{{ $settings['about_url'] ?: url('/about-us') }}" class="btn-outline-text hav-icon mt-2 group text-white!">
                            <span class="underline">
                                More About Us
                            </span>

                            <span
                                class="inline-block ml-1 text-xl transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1">
                            </span>
                        </a>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-5">
                    <div class="homepage__about-media">
                        <div class="homepage__about-image">
                            <div class="placeholder__img-wrapper">
                                <div class="placeholder__img">
                                    @if($homepageImages->isNotEmpty())<a href="{{ $homepageImages->first()->url() }}" data-home-gallery-open aria-label="Open gallery image">@endif
                                    <img id="homepage-about-main-image" @class(['logo-placeholder' => $homepageImages->isEmpty()]) src="{{ $homepageImages->first()?->url() ?: asset('front/images/placeholder-logo.svg') }}" alt="{{ $homepageImages->first()?->alt_text ?: ($homepageContent?->title ?? $settings['about_title']) }}" width="600" height="475" loading="lazy" decoding="async">
                                    @if($homepageImages->isNotEmpty())</a>@endif
                                </div>
                            </div>
                        </div>
                        <div class="homepage__about-awards" aria-label="PCGG gallery images">
                            <button type="button" class="homepage__about-award-prev" aria-label="Previous gallery image">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
                            </button>
                            <div class="swiper homepage-about-award-swiper">
                                <div class="swiper-wrapper">
@foreach($homepageImages as $image)
<div class="swiper-slide">
                                        <a class="homepage__about-award-item {{ $loop->first ? 'is-active' : '' }}" href="{{ $image->url() }}" data-home-gallery data-fancybox="homepage-gallery" data-caption="{{ e($image->alt_text ?: $settings['about_title']) }}" data-gallery-alt="{{ $image->alt_text ?: ($homepageContent?->title ?? $settings['about_title']) }}" aria-label="Show gallery image {{ $loop->iteration }}" >
                                            <x-front.image :media="$image" :alt="$image->alt_text ?: ($homepageContent?->title ?? $settings['about_title'])" width="107" height="107" />
                                        </a>
                                    </div>
@endforeach
</div>
                            </div>
                            <button type="button" class="homepage__about-award-next" aria-label="Next gallery image">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- WHY CHOOSE US -->
            <div class="homepage__whyus scroll-wrap common-box pb-0">
                <div class="homepage__whyus-heading">OUR SERVICES</div>

                <div class="grid grid-cols-12 gap-7.5">
@foreach($settings['homepage_services'] as $service)
<div class="col-span-3">
                        <div class="whyus__item">

                            <div class="whyus__item-icon">
                                <img src="{{ asset('front/images/svg/'.$service['icon']) }}" width="40" height="40" alt="{{ $service['title'] }}" loading="lazy">
                            </div>

                            <div class="whyus__item-content">

                                <div class="whyus__item-title">
{{ $service['title'] }}
</div>

                                <div class="whyus__item-desc">
{{ $service['description'] }}
</div>

                            </div>
                        </div>
                    </div>
@endforeach
</div>
            </div>
        </div>
    </div>
</div>
