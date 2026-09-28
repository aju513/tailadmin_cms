<tr
    data-menu-item-id="{{ $item->id }}"
    data-parent-id="{{ $item->parent_id }}"
    data-depth="{{ $depth }}"
    draggable="false"
    @dragstart="start($event)"
    @dragover="over($event)"
    @drop="drop($event)"
    @dragend="end($event)"
    class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]"
>
    <td data-serial class="w-16 px-4 py-4 text-center text-gray-500"></td>
    <td class="w-16 px-4 py-4 text-center text-gray-400">
        <button type="button" data-drag-handle @mousedown="$el.closest('tr').draggable = true; $el.closest('tr').dataset.dragEnabled = 'true'" class="inline-flex cursor-grab items-center justify-center rounded p-1 hover:bg-gray-100 active:cursor-grabbing dark:hover:bg-gray-800" title="Drag to reorder" aria-label="Drag {{ $item->label }} to reorder">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="5 9 2 12 5 15" />
                <polyline points="9 5 12 2 15 5" />
                <polyline points="15 19 12 22 9 19" />
                <polyline points="19 9 22 12 19 15" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <line x1="12" y1="2" x2="12" y2="22" />
            </svg>
        </button>
    </td>
    <td class="w-14 px-4 py-4 text-center">
        <input type="checkbox" value="{{ $item->id }}" x-model="selected" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600" aria-label="Select {{ $item->label }}">
    </td>
    <td class="px-4 py-4 font-medium text-gray-800 dark:text-white" style="padding-left: {{ 1 + $depth * 1.5 }}rem">
        @if($depth > 0)<span class="mr-1 text-gray-400" aria-hidden="true">&rdsh;</span>@endif
        {{ $item->label }}
    </td>
    <td class="px-4 py-4 text-gray-500 dark:text-gray-400">{{ $item->page?->path ?? $item->external_url }}</td>
    <td class="px-4 py-4 text-right whitespace-nowrap">
        <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove this menu item?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20">
                <x-common.menu-icon name="delete" class="h-4 w-4" />
                Delete
            </button>
        </form>
    </td>
</tr>
@foreach($allItems->where('parent_id', $item->id) as $child)
    @include('pages.admin.menus._item-row', ['item' => $child, 'allItems' => $allItems, 'depth' => $depth + 1])
@endforeach
