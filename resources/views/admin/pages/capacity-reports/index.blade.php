@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Capacity Reports">
    <x-slot:actions>
        @can('capacity-reports.create')
            <a href="{{ route('admin.capacity-reports.create') }}" class="inline-flex h-11 items-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="size-4" />Add Report</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
<div class="space-y-6">
    <x-common.component-card title="Contribution by fiscal year" desc="Manage the two contribution cards shown on the homepage. Each fiscal year has one report.">
        <form method="GET" action="{{ route('admin.capacity-reports.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-xs"><x-form.select name="fiscal_year" label="Fiscal year" :options="$fiscalYears" :value="request('fiscal_year')" placeholder="All fiscal years" /></div>
            <div class="flex gap-3"><x-ui.button type="submit" variant="outline">Filter</x-ui.button><a href="{{ route('admin.capacity-reports.index') }}" class="inline-flex h-11 items-center px-3 text-sm text-gray-500 hover:text-brand-500">Reset</a></div>
        </form>
    </x-common.component-card>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-medium uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"><tr><th class="px-6 py-4">Fiscal year</th><th class="px-6 py-4">Development keys</th><th class="px-6 py-4">Collaboration keys</th><th class="px-6 py-4">Updated</th><th class="px-6 py-4 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $record)
                        <tr class="text-gray-600 dark:text-gray-400">
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-white/90">{{ $record->fiscal_year }}</td>
                            <td class="px-6 py-4">{{ count($record->development) }}</td><td class="px-6 py-4">{{ count($record->collaboration) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $record->updated_at->format('d M Y') }}</td>
                            <td class="px-6 py-4"><div class="flex items-center justify-end gap-3">
                                @can('capacity-reports.edit')<a href="{{ route('admin.capacity-reports.edit', $record) }}" class="text-brand-500 hover:text-brand-600" aria-label="Edit report for {{ $record->fiscal_year }}">Edit</a>@endcan
                                @can('capacity-reports.delete')<form method="POST" action="{{ route('admin.capacity-reports.destroy', $record) }}" onsubmit="return confirm('Delete this fiscal-year report?')">@csrf @method('DELETE')<button type="submit" class="text-error-500 hover:text-error-600" aria-label="Delete report for {{ $record->fiscal_year }}">Delete</button></form>@endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ request('fiscal_year') ? 'No report for this fiscal year.' : 'No capacity reports yet. Add a report to enter contribution figures.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">{{ $records->links() }}</div>@endif
    </div>
</div>
@endsection
