@if($trainingCatalogue['enabled'])
<section class="homepage__traininglist hav-title-btn common-box" aria-labelledby="ongoing-trainings-title">
    <div class="container-fluid">
        <div class="container max-md:!px-0">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <h2 id="ongoing-trainings-title" class="section-title">Ongoing Trainings</h2>
                <div class="section-title-btn">
                    <a href="{{ $trainingCatalogue['url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outline-secondary hav-icon px-4 rounded-lg group">
                        View All Trainings
                        <span class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
            @if(! $trainingCatalogue['available'])
                <p class="px-4 py-6 text-text_color">Training information is temporarily unavailable. Please check back later.</p>
            @elseif($trainingCatalogue['items'] === [])
                <p class="px-4 py-6 text-text_color">No ongoing trainings are available right now.</p>
            @else
                <div class="training-list">
                    @foreach($trainingCatalogue['items'] as $training)
                        @include('front.components.training_item', ['training' => $training])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif
