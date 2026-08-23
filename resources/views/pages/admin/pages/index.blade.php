@extends('layouts.app')

@section('content')
<div x-data="{ selected: [] }">
<x-common.page-breadcrumb pageTitle="Pages">
    <x-slot:actions>
        @can('pages.create')
            <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Create page</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>

<x-common.component-card title="Page manager" desc="Drag rows to reorder pages. Nested pages are shown with -- indentation.">
    <form method="GET" action="{{ route('admin.pages.index') }}" class="mb-6 grid gap-3 sm:grid-cols-[1fr_180px_220px_auto]">
        <input name="search" value="{{ request('search') }}" placeholder="Search title or path" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
        <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">All statuses</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
        </select>
        <select name="page_type" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">All page types</option>
            @foreach($pageTypes as $pageType)
                <option value="{{ $pageType->value }}" @selected(request('page_type') === $pageType->value)>{{ $pageType->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-white">Filter</button>
    </form>

    <div x-data="pageOrdering('{{ route('admin.pages.order') }}')">
            <div x-show="message" x-text="message" class="mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700" x-cloak></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="w-8 px-1 py-3">Order</th><th class="w-10 px-1 py-3">S.N.</th><th class="px-2 py-3">Status</th><th class="px-2 py-3"><input type="checkbox" class="rounded border-gray-300 text-brand-500" @change="selected = $event.target.checked ? @js($pages->pluck('id')->map(fn ($id) => (string) $id)->values()) : []" :checked="selected.length === {{ $pages->count() }} && selected.length > 0" aria-label="Select all pages"></th><th class="px-3 py-3">Page</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Path</th><th class="px-3 py-3">Created date</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
                    <tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pages as $page)
                            @include('pages.admin.pages._row', ['page' => $page, 'rowNumber' => $loop->iteration])
                        @empty
                            <tr><td colspan="9" class="px-4 py-10 text-center text-sm text-gray-500">No pages found.</td></tr>
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
