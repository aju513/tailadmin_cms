@extends('admin.layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Grievances" />
<x-common.component-card title="Grievance submissions" desc="Review messages submitted through published grievance pages.">
    <form method="GET" action="{{ route('admin.grievances.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1"><x-form.input name="search" label="Search" :value="request('search')" maxlength="100" placeholder="Reference, name, email or subject" /></div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
    </form>
    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
        <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-3 py-3">Reference / Subject</th><th class="px-3 py-3">Submitted by</th><th class="px-3 py-3">Submitted at</th><th class="px-3 py-3">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($items as $item)
                <tr><td class="px-3 py-4"><div class="font-medium text-gray-800 dark:text-white">{{ $item->subject ?: 'No subject' }}</div><div class="mt-1 text-xs text-gray-500">{{ $item->reference }}</div></td><td class="px-3 py-4 text-sm text-gray-500">{{ $item->full_name ?: 'Anonymous' }}@if($item->email)<div>{{ $item->email }}</div>@endif</td><td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $item->created_at->format('d M Y H:i') }}</td><td class="px-3 py-4">@can('grievances.show')<a class="text-sm font-medium text-brand-500 hover:text-brand-600" href="{{ route('admin.grievances.show', $item->id) }}">View details</a>@endcan</td></tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No grievances found.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="mt-5">{{ $items->links() }}</div>
</x-common.component-card>
@endsection
