<div class="min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $menu->isImportantLinks() ? 'External links' : 'Menu structure' }}</h3>
            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $menu->isImportantLinks() ? 'Drag the handles to change the link order.' : 'Drag the handles to reorder items at the same level.' }}</p>
        </div>
        @unless($menu->isImportantLinks())
            <x-form.toggle :name="'show_submenus_'.$menu->id" label="Show submenu" :checked="true" @change="showSubmenus = $event.target.checked" />
        @endunless
        <form method="POST" action="{{ route('admin.menus.bulk-destroy') }}" onsubmit="return confirm('Remove the selected items from this menu?')" class="flex shrink-0 items-center gap-3">
            @csrf
            @method('DELETE')
            <input type="hidden" name="menu_id" value="{{ $menu->id }}">
            <span x-show="selected.length" x-cloak x-text="`${selected.length} selected`" class="text-xs font-medium text-gray-500 dark:text-gray-400" aria-live="polite"></span>
            <template x-for="itemId in selected" :key="`bulk-${itemId}`"><input type="hidden" name="menu_items[]" :value="itemId"></template>
            <button type="submit" :disabled="selected.length === 0" class="inline-flex items-center justify-center gap-2 rounded-lg border border-error-200 bg-error-50 px-3 py-2.5 text-xs font-medium text-error-600 transition hover:bg-error-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                <x-common.menu-icon name="delete" class="h-4 w-4" />Bulk Delete
            </button>
        </form>
    </div>

    <div x-show="message" x-text="message" :class="failed ? 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400' : 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'" class="border-b border-gray-100 px-5 py-3 text-sm dark:border-gray-800" role="status" aria-live="polite" x-cloak></div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] table-fixed text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50/80 text-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                <tr>
                    <th scope="col" aria-label="Select menu items" class="w-14 py-3 pl-4 pr-2">
                        <x-common.table-checkbox @change="toggleAll($event.target.checked)" x-bind:checked="allSelected" x-bind:disabled="itemIds.length === 0" x-effect="$el.indeterminate = selected.length > 0 && !allSelected" aria-label="Select all menu items" />
                    </th>
                    <th scope="col" class="w-20 px-2 py-3">Order</th>
                    <th scope="col" class="px-4 py-3">Menu item</th>
                    <th scope="col" class="w-20 py-3 pl-2 pr-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($menu->items->filter(fn ($item) => ! $item->parent_id || ! $menu->items->contains('id', $item->parent_id)) as $item)
                    @include('admin.pages.menus._item-row', ['item' => $item, 'allItems' => $menu->items, 'depth' => 0])
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-14 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-gray-400 dark:border-gray-700 dark:bg-gray-800"><x-common.menu-icon name="menus" /></span>
                            <p class="mt-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $menu->isImportantLinks() ? 'No external links yet' : 'No menu items yet' }}</p>
                            <p class="mx-auto mt-1 max-w-xs text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $menu->isImportantLinks() ? 'Add your first external link using the form.' : 'Assign pages or add a custom link to start building this menu.' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($menu->items->isNotEmpty())
        <div class="flex items-center gap-2 border-t border-gray-100 px-5 py-3 text-xs leading-5 text-gray-500 dark:border-gray-800 dark:text-gray-400">
            <x-common.menu-icon name="activate" class="h-4 w-4 text-gray-400" />Changes to menu order are saved automatically.
        </div>
    @endif
</div>
