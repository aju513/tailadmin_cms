@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Dashboard">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-end gap-3">
            <x-form.select name="days" label="Reporting period" :value="$dashboard['days']" :options="[7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days']" />
            <x-ui.button type="submit" size="sm">Apply</x-ui.button>
        </form>
    </x-slot:actions>
</x-common.page-breadcrumb>
@php($analytics = $dashboard['analytics'])
@php($search = $dashboard['search'])
<div class="space-y-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $dashboard['start'] }} &ndash; {{ $dashboard['end'] }} · Reports refresh every 15 minutes. Recent Google data may be delayed.</p>
    @if (! $analytics['available'])
        <p class="rounded-xl border border-warning-500/30 bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">Google Analytics unavailable. Check the configured property and service account access.</p>
    @endif
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach (['active' => ['Active Users', 'bg-brand-50 text-brand-500 dark:bg-brand-500/15'], 'new' => ['New Visitors', 'bg-warning-50 text-warning-500 dark:bg-warning-500/15'], 'returning' => ['Returning Visitors', 'bg-success-50 text-success-500 dark:bg-success-500/15']] as $metric => [$label, $color])
            <div class="flex items-center gap-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl {{ $color }}"><x-common.menu-icon name="users" class="h-7 w-7" /></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-1 text-3xl font-bold text-gray-800 dark:text-white">{{ $analytics['available'] ? number_format($analytics['data'][$metric]) : '—' }}</p></div>
            </div>
        @endforeach
    </div>
    <div class="grid gap-6 xl:grid-cols-3">
        @foreach (['countries' => ['Page views by country', 'country', 'xl:col-span-2'], 'devices' => ['Top devices', 'device', '']] as $series => [$heading, $chart, $span])
            <x-common.component-card :title="$heading" :class="$span">
                @if ($analytics['available'] && array_sum(array_column($analytics['data'][$series], 'value')) > 0)
                    <div data-dashboard-chart="{{ $chart }}" data-series="{{ json_encode($analytics['data'][$series]) }}" class="min-h-80" role="img" aria-label="{{ $heading }}"></div>
                    <details class="text-sm text-gray-500 dark:text-gray-400"><summary class="cursor-pointer">View data</summary><ul class="mt-3 space-y-1">@foreach ($analytics['data'][$series] as $item)<li>{{ $item['label'] }}: {{ number_format($item['value']) }} {{ $series === 'countries' ? 'page views' : 'active users' }}</li>@endforeach</ul></details>
                @else
                    <p class="py-24 text-center text-sm text-gray-500 dark:text-gray-400">{{ $analytics['available'] ? 'No data for this period.' : 'Report unavailable.' }}</p>
                @endif
            </x-common.component-card>
        @endforeach
    </div>
    <div class="grid gap-6 xl:grid-cols-3">
        <x-common.component-card title="Top 15 Most Visited Pages" class="xl:col-span-2">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800"><tr><th class="px-3 py-3">S.N.</th><th class="px-3 py-3">Page Title</th><th class="px-3 py-3 text-right">Page Views</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($analytics['data']['pages'] ?? [] as $page)
                        <tr><td class="px-3 py-4">{{ $loop->iteration }}.</td><td class="px-3 py-4">@if ($page['url'])<a href="{{ $page['url'] }}" target="_blank" rel="noopener noreferrer" class="text-brand-500 hover:underline">{{ $page['title'] ?: $page['url'] }}</a>@else{{ $page['title'] }}@endif</td><td class="px-3 py-4 text-right font-semibold">{{ number_format($page['views']) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-10 text-center">{{ $analytics['available'] ? 'No page views for this period.' : 'Page report unavailable.' }}</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </x-common.component-card>
        <x-common.component-card title="Top 15 Search Queries" desc="Google Search Console · Web search">
            @if (! $search['available'])
                <p class="mb-4 text-sm text-warning-600 dark:text-warning-400">Search Console unavailable. Check the configured site and service account access.</p>
            @endif
            <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800"><tr><th class="px-3 py-3">Query</th><th class="px-3 py-3 text-right">Clicks</th><th class="px-3 py-3 text-right">Impressions</th><th class="px-3 py-3 text-right">CTR</th><th class="px-3 py-3 text-right">Position</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($search['data'] as $query)
                        <tr><td class="px-3 py-4">{{ $query['query'] }}</td><td class="px-3 py-4 text-right font-semibold">{{ number_format($query['clicks']) }}</td><td class="px-3 py-4 text-right">{{ number_format($query['impressions']) }}</td><td class="px-3 py-4 text-right">{{ $query['ctr'] }}%</td><td class="px-3 py-4 text-right">{{ $query['position'] }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-10 text-center">{{ $search['available'] ? 'No search queries for this period.' : 'Search query report unavailable.' }}</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </x-common.component-card>
    </div>
</div>
@endsection
