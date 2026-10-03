@extends('admin.layouts.app')

@section('content')
<div x-data="pageManager(@js($records->mapWithKeys(fn ($record) => [(string) $record->id => $record->status->value])->all()), 'records')">
    <x-common.page-breadcrumb pageTitle="Galleries">
        <x-slot:actions>
            @can('gallery.publish')
                <form method="POST" action="{{ route('admin.gallery.bulk-status') }}" @submit.prevent="changeStatus($el.action, 'PATCH', [...selected], $event.submitter?.value)" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <template x-for="recordId in selected" :key="`status-${recordId}`"><input type="hidden" name="records[]" :value="recordId"></template>
                    <button name="status" value="published" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="activate" class="h-4 w-4" />Publish</button>
                    <button name="status" value="draft" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Unpublish</button>
                </form>
            @endcan
            @can('gallery.create')
                <a href="{{ route('admin.gallery.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add Gallery</a>
            @endcan
            @can('gallery.delete')
                <form method="POST" action="{{ route('admin.gallery.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected gallery?')" class="flex items-center">
                    @csrf @method('DELETE')
                    <template x-for="recordId in selected" :key="`delete-${recordId}`"><input type="hidden" name="records[]" :value="recordId"></template>
                    <button type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Bulk delete</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-common.page-breadcrumb>
    <x-common.table-status-feedback />

    <x-common.component-card title="Gallery manager" desc="Use the selection controls for bulk actions.">
        <form method="GET" action="{{ route('admin.gallery.index') }}" class="mb-6 grid gap-3 sm:grid-cols-[1fr_auto]">
            <input name="search" value="{{ request('search') }}" aria-label="Search gallery" placeholder="Search gallery" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
            <button class="rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-white">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead><tr class="text-left text-xs uppercase text-gray-500">
                    <th scope="col" aria-label="Reorder" class="w-10 px-2 py-3"></th>
                    <th scope="col" class="w-16 px-2 py-3 text-center">Status</th>
                    <th scope="col" class="w-12 px-2 py-3 text-center"><x-common.table-select-all aria-label="Select all displayed gallery" /></th>
                    <th scope="col" class="px-3 py-3">Title</th>
                    <th scope="col" class="px-3 py-3 text-right">Created date / Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $record)
                        <tr @can('gallery.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.gallery.edit', $record) }}'" @endcan class="@can('gallery.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan bg-white transition dark:bg-transparent">
                            <td class="w-10 px-2 py-4 text-center text-gray-400"><i class="bi bi-arrows-move" aria-hidden="true"></i></td>
                            <td class="w-16 px-2 py-4 text-center" @mousedown.stop><x-common.table-status :id="$record->id" :status="$record->status->value" :label="$record->title" permission="gallery.publish" :url="route('admin.gallery.bulk-status')" selection-key="records" active-value="published" inactive-value="draft" /></td>
                            <td class="w-12 px-2 py-4 text-center"><x-common.table-checkbox value="{{ $record->id }}" x-model="selected" aria-label="Select {{ $record->title }}" @dragstart.stop.prevent /></td>
                            <td class="px-3 py-4"><div class="flex items-center gap-3">
                                @if($record->coverMedia)<img src="{{ $record->coverMedia->url() }}" alt="" class="h-12 w-16 rounded-lg object-cover">@endif
                                <div><div class="font-medium text-gray-800 dark:text-white">{{ $record->title }}</div><div class="mt-1 text-xs text-gray-500">{{ $record->slug }} · {{ $record->photos_count }} images</div></div>
                            </div></td>
                            <td class="px-3 py-4"><div class="flex items-center justify-end gap-3">
                                <time datetime="{{ $record->created_at?->toDateString() }}" class="whitespace-nowrap text-sm text-gray-500">{{ $record->created_at?->format('M d, Y') }}</time>
                                @can('gallery.edit')<a href="{{ route('admin.gallery.edit', $record) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan
                                @can('gallery.delete')<form method="POST" action="{{ route('admin.gallery.destroy', $record) }}" onsubmit="return confirm('Delete this gallery?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No gallery found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $records->links() }}</div>
    </x-common.component-card>
</div>
@endsection
