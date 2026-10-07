@php
    $displayReports = $capacityReports;
    if (!$displayReports) {
        $emptyMetrics = array_map(fn ($label) => ['key' => $label, 'value' => null], array_values(config('settings.capacity_reports.metrics')));
        $displayReports = [['development' => $emptyMetrics, 'collaboration' => $emptyMetrics]];
    }
@endphp
<section class="homepage__capacity-report common-box" aria-labelledby="capacity-report-title">
    <div class="container">
        <div>
            <div class="capacity-report__top">
                <div class="capacity-report__intro">
                    <h2 id="capacity-report-title" class="capacity-report__title text-text_color!">Our Contribution to Capacity Development</h2>
                    <p>Summary of activities that strengthen governance, build capacity, and improve public services across the province.</p>
                </div>
                @if($capacityReports)
                    <div class="capacity-report__years" role="group" aria-label="Fiscal year">
                        @foreach($capacityReports as $report)
                            <button type="button" class="capacity-report__year {{ $loop->first ? 'is-active' : '' }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="capacity-report-panel-{{ $loop->index }}" data-report-year="{{ $loop->index }}">{{ $report['year'] }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
            @foreach($displayReports as $reportIndex => $report)
                <div id="capacity-report-panel-{{ $reportIndex }}" class="capacity-report__cards" data-report-panel="{{ $reportIndex }}" @if($reportIndex > 0) hidden @endif>
                    @foreach(config('settings.capacity_reports.groups') as $group => $definition)
                        <article class="capacity-report__card">
                            <header class="capacity-report__card-header"><h3>{{ $definition['title'] }}</h3><p>{{ $definition['description'] }}</p></header>
                            <div class="capacity-report__rows">
                                @foreach(array_chunk($report[$group], 2) as $row)
                                    <div class="capacity-report__row">
                                        @foreach($row as $metric)
                                            <div class="capacity-report__metric"><span>{{ $metric['key'] }}</span><strong>{{ $metric['value'] !== null ? number_format($metric['value']) : '-' }}</strong></div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
