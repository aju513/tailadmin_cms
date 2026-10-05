@php($canReorder = auth()->user()->can($module.'.edit') && !filled(request('search')))
<div>
<x-common.component-card title="Category manager" desc="Drag rows to change their order.">
    <form method="GET" action="{{ route('admin.'.$module.'.index') }}" class="mb-6 flex items-end gap-3">
        <div class="flex-1"><x-form.input name="search" label="Search" :value="request('search')" placeholder="Search by name" /></div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
        @if(filled(request('search')))<a href="{{ route('admin.'.$module.'.index') }}" class="inline-flex h-11 items-center text-sm font-medium text-brand-500">Clear</a>@endif
    </form>
    @if(filled(request('search')))<p class="mb-4 text-sm text-gray-500">Clear the search to reorder all categories.</p>@endif
    <div x-data="categoryOrdering(@js(route('admin.'.$module.'.order')), @js($canReorder))">
        <p x-show="message" x-text="message" role="status" aria-live="polite" class="mb-4 text-sm" :class="failed ? 'text-error-500' : 'text-success-600'" x-cloak></p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead><tr class="text-left text-xs uppercase text-gray-500">
                    <th class="w-16 px-2 py-3 text-center">Status</th><th class="w-12 px-2 py-3 text-center"><x-common.table-select-all aria-label="Select all displayed categories" /></th>
                    <th class="px-3 py-3">Title</th><th class="px-3 py-3 text-right">Created date / Actions</th>
                </tr></thead>
                <tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($records as $record)
                        <tr data-category-id="{{ $record->id }}" :draggable="canReorder && !saving" @dragstart="start($event)" @dragover="over($event)" @drop="drop($event)" @dragend="end()" class="text-sm text-gray-600 dark:text-gray-300">
                            <td class="w-16 px-2 py-4 text-center" @mousedown.stop><x-common.table-status :id="$record->id" :status="$record->is_active ? '1' : '0'" :label="$record->name" :permission="$module.'.edit'" :url="route('admin.'.$module.'.bulk-status')" selection-key="categories" active-value="1" inactive-value="0" active-label="Deactivate" inactive-label="Activate" /></td>
                            <td class="w-10 px-2 py-4"><input type="checkbox" value="{{ $record->id }}" x-model="selected" class="rounded border-gray-300 text-gray-500 dark:border-gray-600" aria-label="Select {{ $record->name }}"></td>
                            <td class="px-3 py-4"><div class="font-medium text-gray-800 dark:text-white">{{ $record->name }}</div>@if($showSlug ?? true)<div class="mt-1 text-xs text-gray-500">{{ $record->slug }}</div>@endif</td>
                            <td class="px-3 py-4"><div class="flex items-center justify-end gap-3"><time datetime="{{ $record->created_at?->toDateString() }}" class="whitespace-nowrap text-sm text-gray-500">{{ $record->created_at?->format('M d, Y') }}</time><div class="flex justify-end gap-2">
                                @can($module.'.edit')<a href="{{ route('admin.'.$module.'.edit', $record) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">Edit</a>@endcan
                                @can($module.'.delete')
                                    <form method="POST" action="{{ route('admin.'.$module.'.destroy', $record) }}" onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
                                    </form>
                                @endcan
                            </div></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-common.component-card>

@once
@push('scripts')
<script>
window.categoryOrdering = (url, canReorder) => ({
    canReorder, dragging: null, saving: false, message: '', failed: false, original: [],
    rows() { return [...this.$refs.rows.querySelectorAll('[data-category-id]')]; },
    start(event) {
        if (!this.canReorder || this.saving) { event.preventDefault(); return; }
        this.original = this.rows();
        this.dragging = event.currentTarget;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', this.dragging.dataset.categoryId);
        this.dragging.classList.add('opacity-50');
    },
    over(event) {
        if (this.dragging && !this.saving) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; }
    },
    drop(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (!this.dragging || this.dragging === target || this.saving) return;
        const after = event.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
        target.parentNode.insertBefore(this.dragging, after ? target.nextSibling : target);
        this.end();
        this.persist();
    },
    end() { this.dragging?.classList.remove('opacity-50'); this.dragging = null; },
    atEdge(row, direction) {
        const rows = this.rows(), index = rows.indexOf(row);
        return index + direction < 0 || index + direction >= rows.length;
    },
    move(row, direction) {
        if (!this.canReorder || this.saving || this.atEdge(row, direction)) return;
        this.original = this.rows();
        const adjacent = this.original[this.original.indexOf(row) + direction];
        if (direction < 0) this.$refs.rows.insertBefore(row, adjacent);
        else this.$refs.rows.insertBefore(adjacent, row);
        this.persist();
    },
    async persist() {
        this.saving = true; this.failed = false; this.message = '';
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ categories: this.rows().map(row => row.dataset.categoryId) })
            });
            if (!response.ok) throw new Error('Could not save category order. Reload and try again.');
            this.message = 'Category order updated.';
        } catch (error) {
            this.original.forEach(row => this.$refs.rows.appendChild(row));
            this.failed = true; this.message = error.message;
        } finally { this.saving = false; }
    }
});
</script>
@endpush
@endonce
</div>
