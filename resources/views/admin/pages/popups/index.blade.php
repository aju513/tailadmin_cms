@extends('admin.layouts.app')
@section('content')
@php($canReorder = auth()->user()->can('popups.edit') && auth()->user()->can('popups.publish') && !request()->filled('search'))
<x-common.page-breadcrumb pageTitle="Popup Manager">
    <x-slot:actions>@can('popups.create')<a href="{{ route('admin.popups.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Add Popup</a>@endcan</x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Announcements" desc="The first published popup with an available image appears on every homepage visit. Clear search to change priority.">
    <form method="GET" action="{{ route('admin.popups.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <x-form.input name="search" label="Search title" :value="request('search')" maxlength="255" />
        <x-ui.button type="submit" size="sm">Search</x-ui.button>
        <a href="{{ route('admin.popups.index') }}" class="text-sm text-brand-500">Clear</a>
    </form>
    <div x-data="recordOrdering(@js(route('admin.popups.order')), @js($canReorder), { label: 'Popup' })">
        <p x-show="message" x-text="message" role="status" aria-live="polite" class="mb-4 text-sm" :class="failed ? 'text-error-500' : 'text-success-600'" x-cloak></p>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-3 py-3">Priority</th><th class="px-3 py-3">Image / Title</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
            <tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($popups as $record)
                <tr data-record-id="{{ $record->id }}" :draggable="canReorder && !saving" @dragstart="start($event)" @dragover="over($event)" @drop="drop($event)" @dragend="end()">
                    <td class="px-3 py-4"><div class="flex gap-2">
                        <button type="button" @click="move($el.closest('tr'), -1)" :disabled="!canReorder || saving || atEdge($el.closest('tr'), -1)" aria-label="Move {{ $record->title }} up" class="rounded-lg border border-gray-300 p-2 text-gray-700 disabled:opacity-30 dark:border-gray-700 dark:text-white">&uarr;</button>
                        <button type="button" @click="move($el.closest('tr'), 1)" :disabled="!canReorder || saving || atEdge($el.closest('tr'), 1)" aria-label="Move {{ $record->title }} down" class="rounded-lg border border-gray-300 p-2 text-gray-700 disabled:opacity-30 dark:border-gray-700 dark:text-white">&darr;</button>
                    </div></td>
                    <td class="px-3 py-4"><div class="flex items-center gap-3">@if($record->media)<img src="{{ $record->media->url() }}" alt="" class="h-12 w-16 rounded-lg object-contain">@endif<span class="font-medium text-gray-800 dark:text-white">{{ $record->title }}</span></div></td>
                    <td class="px-3 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($record->status->value) }}</td>
                    <td class="px-3 py-4"><div class="flex justify-end gap-3">
                        @if(auth()->user()->can('popups.edit') && ($record->status->value !== 'published' || auth()->user()->can('popups.publish')))<a href="{{ route('admin.popups.edit', $record) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-brand-500 dark:border-gray-700">Edit</a>@endif
                        @if(auth()->user()->can('popups.delete') && ($record->status->value !== 'published' || auth()->user()->can('popups.publish')))<form method="POST" action="{{ route('admin.popups.destroy', $record) }}" onsubmit="return confirm('Delete this popup?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-error-50 px-3 py-2 text-sm text-error-600 dark:bg-error-500/10">Delete</button></form>@endif
                    </div></td>
                </tr>
                @empty<tr><td colspan="4" class="px-3 py-10 text-center text-sm text-gray-500">No popups found.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div>
</x-common.component-card>
@endsection
