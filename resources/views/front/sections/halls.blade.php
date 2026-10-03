<section class="homepage__hall-booking" aria-labelledby="hall-booking-title">
    <div class="container-fluid bg-tertiary md:rounded-[40px] overflow-hidden">
        <div class="container max-md:!px-0">
            <div class="flex items-center justify-between">
                    <div class="homepage__hall-booking-copy">
                        <h2 id="hall-booking-title" class="text-white/90!">Professional Spaces<br />for Trainings &amp; Events</h2>
                        <p class="">Fully equipped spaces designed for trainings, workshops, meetings, and official events.</p>
                    <a href="{{ route('public.halls.index') }}" class="btn-primary hav-icon mt-7 bg-white! text-[#164491]!">
                        <span class="font-medium hover:underline!">View Halls</span>
                        <span class="btn-primary__icon icon-arrow-up-right bg-[#10336F]/80!" aria-hidden="true"></span>
                    </a>
                   
                    </div>
                    <div class="homepage__hall-booking-image -mr-[300px] hidden lg:block">
                        <x-front.image :media="$halls->first()?->bannerMedia ?? $halls->first()?->thumbnailMedia" alt="Training hall" width="1200" height="600" fallback="front/images/dynamic/book-a-hall.jpg" />
                    </div>
            </div>
 
        </div>

    </div>

</section>
