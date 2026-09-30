<section class="homepage__hall-booking" aria-labelledby="hall-booking-title">
    <div class="container-fluid bg-primary md:rounded-[40px] overflow-hidden">
        <div class="container max-md:!px-0">
            <div class="flex items-center justify-between">
                    <div class="homepage__hall-booking-copy">
                        <h2 id="hall-booking-title" class="text-white/90!">Professional Spaces<br />for Trainings &amp; Events</h2>
                        <p class="">Fully equipped spaces designed for trainings, workshops, meetings, and official events.</p>
                    <a href="{{ route('public.halls.index') }}" class="btn-primary group flex w-fit px-5! py-3! max-w-[200px]! hover:bg-block/40! mt-5!">
View Halls                        <span class="ml-1 icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
                   
                    </div>
                    <div class="homepage__hall-booking-image -mr-[300px] hidden lg:block">
                        <x-front.image :media="$halls->first()?->bannerMedia ?? $halls->first()?->thumbnailMedia" alt="Training hall" width="1200" height="600" fallback="front/images/dynamic/book-a-hall.jpg" />
                    </div>
            </div>
 
        </div>

    </div>

</section>
