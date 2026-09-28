@extends('layouts.app')

@section('content')
<div x-data="{ selected: [] }">
<x-common.page-breadcrumb pageTitle="Pages">
    <x-slot:actions>
        @can('pages.publish')
            <form method="POST" action="{{ route('admin.pages.bulk-status') }}" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <template x-for="pageId in selected" :key="`status-${pageId}`"><input type="hidden" name="pages[]" :value="pageId"></template>
                <button name="status" value="published" type="submit" :disabled="selected.length === 0" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="activate" class="h-4 w-4" />Publish</button>
                <button name="status" value="draft" type="submit" :disabled="selected.length === 0" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Unpublish</button>
            </form>
        @endcan
        @can('pages.create')
            <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Create page</a>
        @endcan
        @can('pages.delete')
            <form method="POST" action="{{ route('admin.pages.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected pages?')" class="flex items-center">
                @csrf
                @method('DELETE')
                <template x-for="pageId in selected" :key="`delete-${pageId}`"><input type="hidden" name="pages[]" :value="pageId"></template>
                <button type="submit" :disabled="selected.length === 0" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Bulk delete</button>
            </form>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>

<x-common.component-card title="Page manager" desc="Drag rows to reorder pages. Nested pages are shown with -- indentation.">
    <div x-data="pageOrdering('{{ route('admin.pages.order') }}')">
            <div x-show="message" x-text="message" class="mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700" x-cloak></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead><tr class="text-left text-xs uppercase text-gray-500"><th scope="col" aria-label="Reorder pages" class="w-10 px-2 py-3"></th><th class="w-16 px-2 py-3 text-center">Status</th><th scope="col" aria-label="Select pages" class="w-10 px-2 py-3"></th><th class="px-3 py-3">Page</th><th class="px-3 py-3 text-right">Created date / Actions</th></tr></thead>
                    <tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pages as $page)
                            @include('pages.admin.pages._row', ['page' => $page])
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No pages found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</x-common.component-card>
</div>
@endsection

@push('scripts')
<script>
window.pageOrdering = (url) => ({
    dragging: null,
    message: '',
    start(event) {
        this.dragging = event.currentTarget;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', this.dragging.dataset.pageId);
        this.dragging.classList.add('opacity-50');
    },
    over(event) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; },
    drop(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (!this.dragging || this.dragging === target) return;
        const rect = target.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2;
        target.parentNode.insertBefore(this.dragging, after ? target.nextSibling : target);
        this.persist();
    },
    end() { this.dragging?.classList.remove('opacity-50'); this.dragging = null; },
    persist() {
        const pages = [...this.$refs.rows.querySelectorAll('[data-page-id]')].map(row => row.dataset.pageId);
        fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ pages }) })
            .then(response => { if (!response.ok) throw new Error(); return response; })
            .then(() => { this.message = 'Page order updated.'; setTimeout(() => this.message = '', 2500); })
            .catch(() => { this.message = 'Unable to update page order.'; });
    }
});
</script>
@endpush
