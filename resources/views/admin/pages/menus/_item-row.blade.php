<tr
    data-menu-item-id="{{ $item->id }}"
    data-parent-id="{{ $item->parent_id }}"
    data-depth="{{ $depth }}"
    @if($depth > 0) x-show="showSubmenus" @endif
    draggable="false"
    @dragstart="start($event)"
    @dragover="over($event)"
    @drop="drop($event)"
    @dragend="end($event)"
    class="transition hover:bg-gray-50/80 dark:hover:bg-white/[0.02]"
>
    <td class="py-4 pl-4 pr-2 align-middle">
        <x-common.table-checkbox value="{{ $item->id }}" x-model="selected" aria-label="Select {{ $item->label }}" @dragstart.stop.prevent="" />
    </td>
    <td class="px-2 py-4 align-middle">
        <div class="flex items-center gap-1">
            <span data-serial class="w-6 shrink-0 text-center text-xs tabular-nums text-gray-400"></span>
            <button type="button" data-drag-handle @mousedown="$el.closest('tr').draggable = true; $el.closest('tr').dataset.dragEnabled = 'true'" class="inline-flex h-8 w-8 shrink-0 cursor-grab items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 active:cursor-grabbing dark:hover:bg-gray-800 dark:hover:text-gray-300" title="Drag to reorder" aria-label="Drag {{ $item->label }} to reorder">
                <i class="bi bi-arrows-move" aria-hidden="true"></i>
            </button>
        </div>
    </td>
    <td class="py-4 pr-4 align-middle" style="padding-left: {{ 1 + $depth * 1.25 }}rem">
        <div class="flex min-w-0 items-start gap-3">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $depth > 0 ? 'bg-gray-50 text-gray-400 dark:bg-gray-800 dark:text-gray-500' : 'bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400' }}">
                @if($item->page_id)
                    <x-common.menu-icon name="pages" class="h-4 w-4" />
                @else
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 1 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" /></svg>
                @endif
            </span>
            <div class="min-w-0">
                <p class="font-medium leading-5 text-gray-800 [overflow-wrap:anywhere] dark:text-white/90">{{ $item->label }}</p>
                <p class="mt-1 text-xs leading-5 text-gray-500 [overflow-wrap:anywhere] dark:text-gray-400">{{ $item->page?->path ?? $item->external_url }}</p>
                @if($depth > 0)
                    <span class="mt-1 inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500"><span aria-hidden="true">&rdsh;</span>Submenu item</span>
                @endif
            </div>
        </div>
    </td>
    <td class="py-4 pl-2 pr-5 text-right align-middle">
        <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="inline-flex" onsubmit="return confirm('Remove this menu item?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:text-gray-500 dark:hover:bg-error-500/10 dark:hover:text-error-400" title="Delete menu item" aria-label="Delete {{ $item->label }}">
                <x-common.menu-icon name="delete" class="h-4 w-4" />
            </button>
        </form>
    </td>
</tr>
@foreach($allItems->where('parent_id', $item->id) as $child)
    @include('admin.pages.menus._item-row', ['item' => $child, 'allItems' => $allItems, 'depth' => $depth + 1])
@endforeach
