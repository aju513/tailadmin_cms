@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Notices">
    <x-slot:actions>
        @can('notices.create')
            <a href="{{ route('admin.notices.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Add Notice</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Manage notices">
    <form method="GET" action="{{ route('admin.notices.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="flex-1"><x-form.input name="search" label="Search" :value="request('search')" placeholder="Search by title" /></div>
        <div class="w-56"><x-form.select name="notice_type" label="Notice type" :options="$types" :value="request('notice_type')" placeholder="All types" /></div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Deadline</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($items as $item)
                <tr class="text-sm text-gray-600 dark:text-gray-300">
                    <td class="px-4 py-4"><div class="flex items-center gap-3">
                        <div><div class="font-medium text-gray-800 dark:text-white/90">{{ $item->title }}</div><div class="mt-1 text-xs text-gray-500">{{ $item->notice_type->label() }}</div></div>
                    </div></td>
                    <td class="px-4 py-4">@if($item->deadline_at)
                            {{ $item->deadline_at->format('d M Y H:i') }}
                            <div class="mt-1 text-xs text-gray-500">{{ $item->deadline_at->isPast() ? 'Deadline passed' : 'Upcoming deadline' }}</div>
                        @else<span class="text-gray-400">?</span>@endif</td>
                    <td class="px-4 py-4">
                        <x-ui.badge :color="$item->status->value === 'published' ? 'success' : 'warning'">{{ ucfirst($item->status->value) }}</x-ui.badge>
                        @if($item->status->value === 'published' && $item->published_at?->isFuture())
                            <div class="mt-1 text-xs text-gray-500">Scheduled {{ $item->published_at->format('d M Y H:i') }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-4"><div class="flex justify-end gap-2">
                        @can('notices.edit')
                            <a href="{{ route('admin.notices.edit', $item) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">Edit</a>
                        @endcan
                        @can('notices.delete')
                            @if($item->status->value !== 'published' || auth()->user()->can('notices.publish'))
                            <form method="POST" action="{{ route('admin.notices.destroy', $item) }}" onsubmit="return confirm('Delete this notice? Uploaded files will remain in the Media Library.')">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
                            </form>
                            @endif
                        @endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No notices found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $items->links() }}</div>
</x-common.component-card>
@endsection
