<div @class(['homepage__banner', 'homepage__banner--empty' => $bannerSlides->isEmpty()])>
    @if($bannerSlides->isNotEmpty())
        <div class="homepage__banner-shell">
            <div class="homepage__banner-content">
                <div class="homepage__banner-copy">
                    <h1>{{ $settings['hero_title'] ?: config('frontend.hero_title') }}</h1>
                    <p class="homepage__banner-description">{{ $settings['hero_description'] ?: config('frontend.hero_description') }}</p>
                    <div class="homepage__banner-actions">
                        <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" target="_blank" rel="noopener noreferrer" class="btn-primary group px-5! py-3! max-w-[200px]! hover:bg-secondary! bg-primary!">
                            Apply Roaster
                            <span class="ml-1 icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1" aria-hidden="true"></span>
                        </a>
                        <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" target="_blank" rel="noopener noreferrer" class="btn-primary group px-5! py-3! max-w-[200px]! hover:bg-primary!">
                            View Trainings
                            <span class="ml-1 icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="homepage__banner-image">
                <div class="swiper homepage-banner-swiper" role="region" aria-label="Homepage slides" aria-roledescription="carousel">
                    <div class="swiper-wrapper">
                        @foreach($bannerSlides as $slide)
                            <div class="swiper-slide homepage__banner-slide">
                                <x-front.image :media="$slide['media']" :alt="$slide['title']" :priority="$loop->first" sizes="(min-width: 1024px) 50vw, 100vw" width="1600" height="900" />
                                <span class="homepage__banner-caption">{{ $slide['title'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($bannerSlides->count() > 1)
                        <div class="homepage__banner-pagination swiper-pagination" role="group" aria-label="Choose a home slide"></div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="homepage__notice-bar" role="region" aria-label="Latest notice">
        <a class="homepage__notice-label" href="{{ route('public.notices.index') }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v14H4zM8 3v5m8-5v5M4 10h16" />
            </svg>
            <span>Notices</span>
        </a>
        <a class="homepage__notice-item" href="{{ $latestNotices->first() ? route('public.notices.show', $latestNotices->first()->slug) : route('public.notices.index') }}"><span class="homepage__notice-title">{{ $latestNotices->first()?->title ?? 'No notices published yet.' }}</span></a>
        <a class="btn-secondary hav-icon homepage__notice-all" href="{{ route('public.notices.index') }}">
            <span>View all notices</span>
            <span class="btn-secondary__icon icon-arrow-up-right" aria-hidden="true"></span>
        </a>
    </div>
</div>
