@extends('admin.layouts.app')

@section('content')
<div x-data="pageManager(@js($records->mapWithKeys(fn ($record) => [(string) $record->id => $record->status->value])->all()), 'resources')">
<x-common.page-breadcrumb pageTitle="Resources">
    <x-slot:actions>
        @can('resources.publish')
            <form method="POST" action="{{ route('admin.resources.bulk-status') }}" @submit.prevent="changeStatus($el.action, 'PATCH', [...selected], $event.submitter?.value)" class="flex items-center gap-2">@csrf @method('PATCH')<template x-for="resourceId in selected" :key="`status-${resourceId}`"><input type="hidden" name="resources[]" :value="resourceId"></template><button name="status" value="published" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="activate" class="h-4 w-4" />Publish</button><button name="status" value="draft" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Unpublish</button></form>
        @endcan
        @can('resources.create')<a href="{{ route('admin.resources.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add Resource</a>@endcan
        @can('resources.delete')<form method="POST" action="{{ route('admin.resources.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected resources?')" class="flex items-center">@csrf @method('DELETE')<template x-for="resourceId in selected" :key="`delete-${resourceId}`"><input type="hidden" name="resources[]" :value="resourceId"></template><button type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Bulk delete</button></form>@endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
    <x-common.table-status-feedback />

<x-common.component-card title="Resource manager" desc="Drag rows to reorder resources. Use the selection controls for bulk actions.">
    <form method="GET" action="{{ route('admin.resources.index') }}" class="mb-6 grid gap-3 sm:grid-cols-[1fr_auto]"><input name="search" value="{{ request('search') }}" placeholder="Search resource" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white"><button class="rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-white">Search</button></form>
    <div x-data="resourceOrdering('{{ route('admin.resources.order') }}')"><div x-show="message" x-text="message" class="mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700" x-cloak></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800"><thead><tr class="text-left text-xs uppercase text-gray-500"><th scope="col" aria-label="Reorder resources" class="w-10 px-2 py-3"></th><th class="w-16 px-2 py-3 text-center">Status</th><th scope="col" class="w-12 px-2 py-3 text-center"><x-common.table-select-all aria-label="Select all displayed resources" /></th><th class="px-3 py-3">Title</th><th class="px-3 py-3 text-right">Created date / Actions</th></tr></thead><tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($records as $record)
            <tr data-resource-id="{{ $record->id }}" draggable="true" @dragstart="start($event)" @dragover="over($event)" @drop="drop($event)" @dragend="end()" @can('resources.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.resources.edit', $record) }}'" @endcan class="@can('resources.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan bg-white transition dark:bg-transparent">
                <td class="w-10 px-2 py-4 text-center text-gray-400"><span class="inline-flex cursor-grab items-center justify-center" title="Drag to reorder" aria-label="Drag to reorder"><i class="bi bi-arrows-move" aria-hidden="true"></i></span></td>
                <td class="w-16 px-2 py-4 text-center" @mousedown.stop><x-common.table-status :id="$record->id" :status="$record->status->value" :label="$record->title" permission="resources.publish" :url="route('admin.resources.bulk-status')" selection-key="resources" active-value="published" inactive-value="draft" /></td>
                <td class="w-12 px-2 py-4 text-center" @mousedown.stop><x-common.table-checkbox value="{{ $record->id }}" x-model="selected" aria-label="Select {{ $record->title }}" @dragstart.stop.prevent /></td>
                <td class="px-3 py-4"><div class="font-medium text-gray-800 dark:text-white">{{ $record->title }}<div class="mt-1 text-xs font-normal text-gray-500">{{ $record->category?->name ?? 'Uncategorized' }} · @if($record->fileMedia)<a href="{{ $record->fileMedia->url() }}" target="_blank" rel="noopener noreferrer" class="text-brand-500 hover:underline" @click.stop>View file</a>@else<span class="text-error-500">Missing attachment</span>@endif</div></div></td>
                <td class="px-3 py-4"><div class="flex items-center justify-end gap-3"><time datetime="{{ $record->created_at?->toDateString() }}" class="whitespace-nowrap text-sm text-gray-500">{{ $record->created_at?->format('M d, Y') }}</time>@can('resources.edit')<a href="{{ route('admin.resources.edit', $record) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan @can('resources.delete')<form method="POST" action="{{ route('admin.resources.destroy', $record) }}" onsubmit="return confirm('Delete this resource? Uploaded files will be preserved.')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan</div></td>
            </tr>
        @empty<tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No resources found.</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div class="mt-5">{{ $records->links() }}</div>
</x-common.component-card>
</div>
@endsection

@push('scripts')
<script>
window.resourceOrdering = (url) => ({
    dragging: null, message: '',
    start(event) { this.dragging = event.currentTarget; event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', this.dragging.dataset.resourceId); this.dragging.classList.add('opacity-50'); },
    over(event) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; },
    drop(event) { event.preventDefault(); const target = event.currentTarget; if (!this.dragging || this.dragging === target) return; const rect = target.getBoundingClientRect(); const after = event.clientY > rect.top + rect.height / 2; target.parentNode.insertBefore(this.dragging, after ? target.nextSibling : target); this.persist(); },
    end() { this.dragging?.classList.remove('opacity-50'); this.dragging = null; },
    persist() { const resources = [...this.$refs.rows.querySelectorAll('[data-resource-id]')].map(row => row.dataset.resourceId); fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ resources }) }).then(response => { if (!response.ok) throw new Error(); return response; }).then(() => { this.message = 'Resource order updated.'; setTimeout(() => this.message = '', 2500); }).catch(() => { this.message = 'Unable to update resource order.'; }); }
});
</script>
@endpush
