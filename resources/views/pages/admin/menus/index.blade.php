@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Public Menus" />

@php
    $menuSections = [
        ['title' => 'Main Menu', 'location' => 'header', 'menus' => $headerMenus],
        ['title' => 'Footer Menu', 'location' => 'footer', 'menus' => $footerMenus],
        ['title' => 'Dynamic Menus', 'location' => null, 'menus' => $dynamicMenus],
    ];
    if ($menuLocation !== null) {
        $menuSections = array_values(array_filter($menuSections, fn (array $section): bool => $section['location'] === $menuLocation));
    }
@endphp

<div class="space-y-8">
    @foreach($menuSections as $section)
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-6 flex flex-col gap-3 border-b border-gray-100 pb-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">{{ $section['title'] }} Manager</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Assign pages, arrange their order, and remove links from this menu position.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $section['menus']->sum(fn ($menu) => $menu->items->count()) }} items</span>
            </div>

            <div class="space-y-8">
                @forelse($section['menus'] as $menu)
                    <div x-data="menuManager(@js(route('admin.menus.order')), {{ $menu->id }})" class="space-y-5">
                        @if($section['location'] === null)
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">{{ $menu->name }}</h3>
                        @endif

                        <form method="POST" action="{{ route('admin.menus.assign') }}" class="rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5 sm:p-5">
                            @csrf
                            <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                <div class="min-w-0 flex-1">
                                    <x-form.multiselect name="page_ids[]" :id="'page-ids-'.$menu->id" label="Assign pages" :options="$availablePages->get($menu->id, collect())->map(fn ($page) => ['value' => $page->id, 'label' => str_repeat('-- ', substr_count($page->path, '/')).$page->title.' ('.$page->path.')'])->all()" placeholder="Search and select pages" help="Select one or more pages. Parent and child pages keep their hierarchy." />
                                </div>
                                <button type="submit" @disabled($availablePages->get($menu->id, collect())->isEmpty()) class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-success-600 disabled:cursor-not-allowed disabled:opacity-50">
                                    <x-common.menu-icon name="create" class="h-4 w-4" />
                                    Assign Menu
                                </button>
                            </div>
                        </form>

                        <div>
                            <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Assigned menu items</h3>
                                    <p class="mt-1 text-xs text-gray-500">Drag the order handle to reorder items within the same parent level.</p>
                                </div>
                                <form method="POST" action="{{ route('admin.menus.bulk-destroy') }}" onsubmit="return confirm('Remove the selected items from this menu?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <template x-for="itemId in selected" :key="`bulk-${itemId}`"><input type="hidden" name="menu_items[]" :value="itemId"></template>
                                    <button type="submit" :disabled="selected.length === 0" class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-50 px-4 py-2.5 text-sm font-medium text-error-600 transition hover:bg-error-100 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10 dark:hover:bg-error-500/20">
                                        <x-common.menu-icon name="delete" class="h-4 w-4" />
                                        Bulk Delete
                                    </button>
                                </form>
                            </div>

                            <div x-show="message" x-text="message" :class="failed ? 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400' : 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'" class="mb-3 rounded-lg px-4 py-3 text-sm" x-cloak></div>

                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-800">
                                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        <tr>
                                            <th class="w-16 px-4 py-3 text-center">S.N.</th>
                                            <th scope="col" aria-label="Menu order" class="w-16 px-4 py-3 text-center">Order</th>
                                            <th scope="col" aria-label="Select menu items" class="w-14 px-4 py-3 text-center">
                                                <input type="checkbox" @change="toggleAll($event.target.checked)" :checked="allSelected" x-effect="$el.indeterminate = selected.length > 0 && !allSelected" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600" aria-label="Select all menu items">
                                            </th>
                                            <th class="px-4 py-3">Menu Title</th>
                                            <th class="px-4 py-3">Page / URL</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody x-ref="rows" class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-transparent">
                                        @forelse($menu->items->filter(fn ($item) => ! $item->parent_id || ! $menu->items->contains('id', $item->parent_id)) as $item)
                                            @include('pages.admin.menus._item-row', ['item' => $item, 'allItems' => $menu->items, 'depth' => 0])
                                        @empty
                                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No items in this menu.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-800">No menu position configured.</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
window.menuManager = (orderUrl, menuId) => ({
    selected: [],
    dragging: null,
    message: '',
    failed: false,
    get itemIds() {
        return [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')].map(row => row.dataset.menuItemId);
    },
    get allSelected() {
        return this.itemIds.length > 0 && this.selected.length === this.itemIds.length;
    },
    toggleAll(checked) {
        this.selected = checked ? this.itemIds : [];
    },
    renumber() {
        [...this.$refs.rows.querySelectorAll('[data-serial]')].forEach((cell, index) => { cell.textContent = `${index + 1}.`; });
    },
    start(event) {
        const row = event.currentTarget;
        if (row.dataset.dragEnabled !== 'true') {
            event.preventDefault();
            return;
        }
        this.dragging = row;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.dataset.menuItemId);
        row.classList.add('opacity-50');
    },
    over(event) {
        if (this.dragging && this.dragging.dataset.parentId === event.currentTarget.dataset.parentId) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }
    },
    drop(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (!this.dragging || this.dragging === target || this.dragging.dataset.parentId !== target.dataset.parentId) return;

        const movingRows = this.blockFor(this.dragging);
        const targetRows = this.blockFor(target);
        const after = event.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
        const anchor = after ? targetRows[targetRows.length - 1].nextElementSibling : targetRows[0];
        if (anchor && movingRows.includes(anchor)) return;

        movingRows.forEach(row => row.remove());
        movingRows.forEach(row => this.$refs.rows.insertBefore(row, anchor));
        this.renumber();
        this.persist(target.dataset.parentId);
    },
    end(event) {
        event.currentTarget.classList.remove('opacity-50');
        event.currentTarget.draggable = false;
        delete event.currentTarget.dataset.dragEnabled;
        this.dragging = null;
    },
    blockFor(row) {
        const rows = [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')];
        const start = rows.indexOf(row);
        const depth = Number(row.dataset.depth);
        const block = [row];
        for (let index = start + 1; index < rows.length && Number(rows[index].dataset.depth) > depth; index++) block.push(rows[index]);
        return block;
    },
    persist(parentId) {
        const itemIds = [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')]
            .filter(row => row.dataset.parentId === parentId)
            .map(row => row.dataset.menuItemId);

        fetch(orderUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ menu_id: menuId, parent_id: parentId || null, menu_items: itemIds }),
        })
            .then(response => { if (!response.ok) throw new Error(); return response.json(); })
            .then(data => {
                this.failed = false;
                this.message = data.message;
                setTimeout(() => this.message = '', 2500);
            })
            .catch(() => {
                this.failed = true;
                this.message = 'Unable to update the menu order. Reloading the saved order.';
                setTimeout(() => window.location.reload(), 1200);
            });
    },
    init() {
        this.$nextTick(() => this.renumber());
    },
});
</script>
@endpush
