<tr class="bg-white dark:bg-gray-900">
    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white" style="padding-left: {{ 1 + $depth * 1.5 }}rem">
        @if($depth > 0)<span class="mr-1 text-gray-400" aria-hidden="true">↳</span>@endif{{ $item->label }}
    </td>
    <td class="px-4 py-3 text-gray-500">{{ $item->page?->path ?? $item->external_url }}</td>
    <td class="px-4 py-3 text-right whitespace-nowrap">
        <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove this menu item?')">
            @csrf @method('DELETE')<button type="submit" class="text-error-600 hover:underline">Delete</button>
        </form>
    </td>
</tr>
@foreach($allItems->where('parent_id', $item->id) as $child)
    @include('pages.admin.menus._item-row', ['item' => $child, 'allItems' => $allItems, 'depth' => $depth + 1])
@endforeach
