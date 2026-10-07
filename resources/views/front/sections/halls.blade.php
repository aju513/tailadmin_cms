<section class="homepage__hall-booking" aria-labelledby="hall-booking-title">
    <div class="homepage__hall-booking-image">
        <x-front.image alt="Training hall arranged for a professional event" width="1200" height="600" fallback="front/images/dynamic/book-a-hall.jpg" />
    </div>
    <div class="container homepage__hall-booking-content">
        <div class="homepage__hall-booking-copy">
            <h2 id="hall-booking-title" class="text-white/90!">Professional Spaces<br />for Trainings &amp; Events</h2>
            <p>Fully equipped spaces designed for trainings, workshops, meetings, and official events.</p>
            <a href="{{ route('public.halls.index') }}" class="btn-venue mt-7">
                <span class="btn-venue__text">View Halls</span>
                <span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</section>
