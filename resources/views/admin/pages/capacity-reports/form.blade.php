@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title">
    <x-slot:actions><x-common.form-actions form-id="capacity-report-form" close-route="admin.capacity-reports.index" close-permission="capacity-reports.manage" submit-label="Save report" /></x-slot:actions>
</x-common.page-breadcrumb>
@php
    $editor = ['maxRows' => config('settings.capacity_reports.max_rows')];
    foreach (['development', 'collaboration'] as $group) {
        $rows = old($group, $report->{$group});
        $editor[$group] = [];
        foreach (is_array($rows) ? array_values($rows) : [['key' => '', 'value' => null]] as $index => $row) {
            $editor[$group][] = ['key' => is_array($row) && is_string($row['key'] ?? null) ? $row['key'] : '', 'value' => is_array($row) && is_scalar($row['value'] ?? null) ? $row['value'] : '', 'keyError' => $errors->first($group.'.'.$index.'.key'), 'valueError' => $errors->first($group.'.'.$index.'.value')];
        }
        if ($editor[$group] === []) $editor[$group][] = ['key' => '', 'value' => '', 'keyError' => '', 'valueError' => ''];
    }
@endphp
<form id="capacity-report-form" method="POST" action="{{ $report->exists ? route('admin.capacity-reports.update', $report) : route('admin.capacity-reports.store') }}" x-data="capacityReportEditor(@js($editor))" class="space-y-6">
    @csrf
    @if($report->exists) @method('PUT') @endif
    <x-common.form-actions form-id="capacity-report-form" close-route="admin.capacity-reports.index" close-permission="capacity-reports.manage" submit-label="Save report" :sticky="true" />
    <x-common.component-card title="Reporting period" desc="Saved figures appear on the homepage under this fiscal year. Leave a value blank to show a dash; enter 0 for a recorded zero.">
        <div class="max-w-sm"><x-form.select name="fiscal_year" label="Fiscal year" :options="$fiscalYears" :value="$report->fiscal_year" placeholder="Select fiscal year" required /></div>
    </x-common.component-card>
    <div class="grid items-start gap-6 xl:grid-cols-2">
        @foreach(config('settings.capacity_reports.groups') as $group => $definition)
            <x-common.component-card :title="$definition['title']" :desc="$definition['description']">
                <div class="space-y-4" data-metric-group="{{ $group }}">
                    <template x-for="(row, index) in {{ $group }}" :key="row.uid">
                        <div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/30">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Entry <span x-text="index + 1"></span></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="remove('{{ $group }}', index)" :disabled="{{ $group }}.length === 1" :aria-label="'Remove ' + (row.key || 'entry')" class="rounded-lg p-2 text-error-500 hover:bg-error-50 disabled:opacity-30 dark:hover:bg-error-500/10"><x-common.menu-icon name="delete" class="size-4" /></button>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_120px]">
                                <div class="space-y-1.5">
                                    <label :for="'metric-{{ $group }}-' + row.uid + '-key'" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Key / Label <span class="text-error-500">*</span></label>
                                    <x-form.input name="{{ $group }}-key" x-bind:id="'metric-{{ $group }}-' + row.uid + '-key'" x-bind:name="'{{ $group }}[' + index + '][key]'" x-model="row.key" @input="row.keyError = ''" maxlength="160" required x-bind:aria-invalid="row.keyError ? 'true' : 'false'" />
                                    <p x-show="row.keyError" x-text="row.keyError" class="text-xs text-error-500" role="alert"></p>
                                </div>
                                <div class="space-y-1.5">
                                    <label :for="'metric-{{ $group }}-' + row.uid + '-value'" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Value</label>
                                    <x-form.input name="{{ $group }}-value" type="number" x-bind:id="'metric-{{ $group }}-' + row.uid + '-value'" x-bind:name="'{{ $group }}[' + index + '][value]'" x-model="row.value" @input="row.valueError = ''" min="0" :max="config('settings.capacity_reports.max_value')" step="1" placeholder="—" x-bind:aria-invalid="row.valueError ? 'true' : 'false'" />
                                    <p x-show="row.valueError" x-text="row.valueError" class="text-xs text-error-500" role="alert"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <x-ui.button type="button" variant="outline" @click="add('{{ $group }}')" x-bind:disabled="{{ $group }}.length >= maxRows">Add key/value</x-ui.button>
                </div>
            </x-common.component-card>
        @endforeach
    </div>
</form>
@endsection
