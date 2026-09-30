<div class="homepage__banner">

    <div class="homepage__banner-shell">
        <div class="homepage__banner-content">
            <div class="homepage__banner-copy">

                <h1>{{ $settings['hero_title'] ?: config('frontend.hero_title') }}</h1>
                <p class="homepage__banner-description">
                    {{ $settings['hero_description'] ?: config('frontend.hero_description') }}
                </p>
                <div class="homepage__banner-actions">
                             <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" target="_blank" rel="noopener noreferrer" class="btn-primary group px-5! py-3! max-w-[200px]! hover:bg-secondary! bg-primary!">
                        Apply Roaster
                        <span class="ml-1 icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
                    <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" target="_blank" rel="noopener noreferrer" class="btn-primary group px-5! py-3! max-w-[200px]! hover:bg-primary!">
                        Explore Trainings
                        <span class="ml-1 icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
               
                </div>

            </div>
        </div>
        <div class="homepage__banner-image">
            <div class="swiper homepage-banner-swiper" role="region" aria-label="PCGG homepage photos" aria-roledescription="carousel">
                <div class="swiper-wrapper">
@forelse($slides as $slide)
<div class="swiper-slide homepage__banner-slide">
                        <x-front.image :media="$slide->media" :alt="$slide->title" :priority="$loop->first" sizes="(min-width: 1024px) 60vw, 100vw" width="1600" height="900" />
                        <span class="homepage__banner-caption">{{ $slide->title }}</span>
                    </div>
@empty
<div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-01.jpg" width="1600" height="900" fetchpriority="high" alt="PCGG grounds and administrative building during a Training of Trainers program inauguration" />
                        <span class="homepage__banner-caption">Training of Trainers Program Inauguration</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-02.jpg" width="1600" height="900" alt="The PCGG library" loading="lazy" />
                        <span class="homepage__banner-caption">PCGG Library</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-03.jpg" width="1600" height="900" alt="Newly appointed Deputy Secretaries at the service entry training certificate ceremony" loading="lazy" />
                        <span class="homepage__banner-caption">Officer Level Service Entry Training</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-04.jpg" width="1600" height="900" alt="State Governance Center training building" loading="lazy" />
                        <span class="homepage__banner-caption">PCGG Training Building</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-05.jpg" width="1600" height="900" alt="Participants at a Training of Trainers certificate distribution" loading="lazy" />
                        <span class="homepage__banner-caption">Training of Trainers Certificate Ceremony</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-06.jpg" width="1600" height="900" alt="Training of Trainers program inauguration at the State Governance Center" loading="lazy" />
                        <span class="homepage__banner-caption">Training at the State Governance Center</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-07.jpg" width="1600" height="900" alt="A PCGG training event" loading="lazy" />
                        <span class="homepage__banner-caption">PCGG Training Program</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-08.jpg" width="1600" height="900" alt="Local economic development and entrepreneurship training" loading="lazy" />
                        <span class="homepage__banner-caption">Local Economic Development Training</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-09.jpg" width="1600" height="900" alt="Women members of the Lumbini State Assembly during a GESI orientation program" loading="lazy" />
                        <span class="homepage__banner-caption">GESI Orientation Program</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-10.jpg" width="1600" height="900" alt="Engineers and sub-engineers attending a residential training" loading="lazy" />
                        <span class="homepage__banner-caption">Engineering and Quality Control Training</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-11.jpg" width="1600" height="900" alt="PCGG administrative building" loading="lazy" />
                        <span class="homepage__banner-caption">Administrative Building</span>
                    </div>
                    <div class="swiper-slide homepage__banner-slide">
                        <img src="/front/images/dynamic/homepage-banner/slide-12.jpg" width="1600" height="900" alt="PCGG training hall arranged for an event" loading="lazy" />
                        <span class="homepage__banner-caption">The Hall</span>
                    </div>
                @endforelse
</div>
                <div class="homepage__banner-pagination swiper-pagination" role="group" aria-label="Choose a banner image"></div>
            </div>
        </div>

    </div>

<div class="homepage__notice-bar" role="region" aria-label="Latest notice">
        <a class="homepage__notice-label" href="{{ route('public.notices.index') }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v14H4zM8 3v5m8-5v5M4 10h16" />
            </svg>
            <span>Notices</span>
        </a>
        <a class="homepage__notice-item" href="{{ $latestNotices->first() ? route('public.notices.show',$latestNotices->first()->slug) : route('public.notices.index') }}"><span class="homepage__notice-title">{{ $latestNotices->first()?->title ?? 'No notices published yet.' }}</span></a>
        <a class="btn-secondary hav-icon homepage__notice-all" href="{{ route('public.notices.index') }}">
            <span>View all notices</span>
            <span class="btn-secondary__icon icon-arrow-up-right" aria-hidden="true"></span>
        </a>
    </div>
    </div>
