<article class="training-list__item">
    <div class="training-list__badges">
        @if($training['delivery_type'])
            <span class="training-list__badge training-list__badge--type">{{ $training['delivery_type'] }}</span>
        @endif
        <span class="training-list__badge training-list__badge--status">Ongoing</span>
    </div>
    <h3 class="training-list__title">
        <a href="{{ $training['url'] }}" target="_blank" rel="noopener noreferrer">{{ $training['name'] }}</a>
    </h3>
    @if($training['description'])
        <div class="training-list__description">{!! $training['description'] !!}</div>
    @endif
    <div class="training-list__meta">
        @if($training['dates'] || $training['dates_bs'])
            <div class="training-list__meta-item">
                <span class="icon-calendar-lines" aria-hidden="true"></span>
                <span>{{ app()->getLocale() === 'ne' && $training['dates_bs'] ? $training['dates_bs'].' BS' : ($training['dates'] ?: $training['dates_bs'].' BS') }}</span>
            </div>
        @endif
        @if($training['venue'])
            <div class="training-list__meta-item">
                <span class="icon-location" aria-hidden="true"></span>
                <span>{{ $training['venue'] }}</span>
            </div>
        @endif
        @if($training['department'])
            <p class="text-sm text-white!">{{ $training['department'] }}</p>
        @endif
    </div>
    <a href="{{ $training['url'] }}" target="_blank" rel="noopener noreferrer" class="btn-venue btn-venue--compact mt-8">
        <span class="btn-venue__text">View Details</span>
        <span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span>
    </a>
</article>
