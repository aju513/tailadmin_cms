<section class="homepage__capacity-report common-box" aria-labelledby="capacity-report-title">
    <div class="container-fluid">
        <div class="sm:container sm:!px-0">
            <div class="capacity-report__top">
                <div class="capacity-report__intro">
                    <h2 id="capacity-report-title" class="capacity-report__title text-text_color!">Our Contribution to Capacity Development</h2>
                    <p>Summary of activities that strengthen governance, build capacity, and improve public services across the province.</p>
                </div>
                <div class="capacity-report__years" role="group" aria-label="Reporting year">
@foreach($settings['capacity_reports'] as $report)
<button type="button" class="capacity-report__year {{ $loop->first ? 'is-active' : '-' }}" aria-pressed="{{ $loop->first ? 'true' : '-' }}" data-report-year="{{ $loop->index }}">{{ $report['year'] }}</button>
@endforeach
</div>
            </div>
            @forelse($settings['capacity_reports'] as $reportIndex=>$report)
<div class="capacity-report__cards" data-report-panel="{{ $reportIndex }}" @if($reportIndex > 0) hidden @endif>
                <article class="capacity-report__card">
                    <header class="capacity-report__card-header">
                        <h3>Capacity Development Contribution</h3>
                        <p>Key figures from training and capacity development programs</p>
                    </header>
                    <div class="capacity-report__rows">
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Total training programs</span><strong>{{ isset($report['development']['training_programs']) ? number_format($report['development']['training_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Total participants</span><strong>{{ isset($report['development']['participants']) ? number_format($report['development']['participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>In-service training programs</span><strong>{{ isset($report['development']['in_service_programs']) ? number_format($report['development']['in_service_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>In-service participants</span><strong>{{ isset($report['development']['in_service_participants']) ? number_format($report['development']['in_service_participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Training materials developed</span><strong>{{ isset($report['development']['materials']) ? number_format($report['development']['materials']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Issue-focused dialogues</span><strong>{{ isset($report['development']['dialogues']) ? number_format($report['development']['dialogues']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Research studies</span><strong>{{ isset($report['development']['research']) ? number_format($report['development']['research']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Consultancy services</span><strong>{{ isset($report['development']['consultancy']) ? number_format($report['development']['consultancy']) : '-' }}</strong></div>
                        </div>
                    </div>
                </article>
                <article class="capacity-report__card">
                    <header class="capacity-report__card-header">
                        <h3>Contribution Through Collaboration</h3>
                        <p>Key figures from working with government agencies and local governments</p>
                    </header>
                    <div class="capacity-report__rows">
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Total training programs</span><strong>{{ isset($report['collaboration']['training_programs']) ? number_format($report['collaboration']['training_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Total participants</span><strong>{{ isset($report['collaboration']['participants']) ? number_format($report['collaboration']['participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>In-service training programs</span><strong>{{ isset($report['collaboration']['in_service_programs']) ? number_format($report['collaboration']['in_service_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>In-service participants</span><strong>{{ isset($report['collaboration']['in_service_participants']) ? number_format($report['collaboration']['in_service_participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Training materials developed</span><strong>{{ isset($report['collaboration']['materials']) ? number_format($report['collaboration']['materials']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Issue-focused dialogues</span><strong>{{ isset($report['collaboration']['dialogues']) ? number_format($report['collaboration']['dialogues']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Research studies</span><strong>{{ isset($report['collaboration']['research']) ? number_format($report['collaboration']['research']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Consultancy services</span><strong>{{ isset($report['collaboration']['consultancy']) ? number_format($report['collaboration']['consultancy']) : '-' }}</strong></div>
                        </div>
                    </div>
                </article>
            </div>
@empty
@php($report = [])
@php($reportIndex = 0)
<div class="capacity-report__cards" data-report-panel="{{ $reportIndex }}" @if($reportIndex > 0) hidden @endif>
                <article class="capacity-report__card">
                    <header class="capacity-report__card-header">
                        <h3>Capacity Development Contribution</h3>
                        <p>Key figures from training and capacity development programs</p>
                    </header>
                    <div class="capacity-report__rows">
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Total training programs</span><strong>{{ isset($report['development']['training_programs']) ? number_format($report['development']['training_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Total participants</span><strong>{{ isset($report['development']['participants']) ? number_format($report['development']['participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>In-service training programs</span><strong>{{ isset($report['development']['in_service_programs']) ? number_format($report['development']['in_service_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>In-service participants</span><strong>{{ isset($report['development']['in_service_participants']) ? number_format($report['development']['in_service_participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Training materials developed</span><strong>{{ isset($report['development']['materials']) ? number_format($report['development']['materials']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Issue-focused dialogues</span><strong>{{ isset($report['development']['dialogues']) ? number_format($report['development']['dialogues']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Research studies</span><strong>{{ isset($report['development']['research']) ? number_format($report['development']['research']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Consultancy services</span><strong>{{ isset($report['development']['consultancy']) ? number_format($report['development']['consultancy']) : '-' }}</strong></div>
                        </div>
                    </div>
                </article>
                <article class="capacity-report__card">
                    <header class="capacity-report__card-header">
                        <h3>Contribution Through Collaboration</h3>
                        <p>Key figures from working with government agencies and local governments</p>
                    </header>
                    <div class="capacity-report__rows">
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Total training programs</span><strong>{{ isset($report['collaboration']['training_programs']) ? number_format($report['collaboration']['training_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Total participants</span><strong>{{ isset($report['collaboration']['participants']) ? number_format($report['collaboration']['participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>In-service training programs</span><strong>{{ isset($report['collaboration']['in_service_programs']) ? number_format($report['collaboration']['in_service_programs']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>In-service participants</span><strong>{{ isset($report['collaboration']['in_service_participants']) ? number_format($report['collaboration']['in_service_participants']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Training materials developed</span><strong>{{ isset($report['collaboration']['materials']) ? number_format($report['collaboration']['materials']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Issue-focused dialogues</span><strong>{{ isset($report['collaboration']['dialogues']) ? number_format($report['collaboration']['dialogues']) : '-' }}</strong></div>
                        </div>
                        <div class="capacity-report__row">
                            <div class="capacity-report__metric"><span>Research studies</span><strong>{{ isset($report['collaboration']['research']) ? number_format($report['collaboration']['research']) : '-' }}</strong></div>
                            <div class="capacity-report__metric"><span>Consultancy services</span><strong>{{ isset($report['collaboration']['consultancy']) ? number_format($report['collaboration']['consultancy']) : '-' }}</strong></div>
                        </div>
                    </div>
                </article>
            </div>
@endforelse
        </div>
    </div>
</section>
