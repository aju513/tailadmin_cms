<tr data-page-id="{{ $page->id }}" draggable="true" @dragstart="start($event)" @dragover="over($event)" @drop="drop($event)" @dragend="end()" @can('pages.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.pages.edit', $page) }}'" @endcan class="@can('pages.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan bg-white transition dark:bg-transparent">
    <td class="w-10 px-2 py-4 text-center text-gray-400">
        <span class="inline-flex cursor-grab items-center justify-center" title="Drag to reorder" aria-label="Drag to reorder">
            <i class="bi bi-arrows-move" aria-hidden="true"></i>
        </span>
    </td>
    <td class="w-16 px-2 py-4 text-center" @mousedown.stop>
        @can('pages.publish')
            <form method="POST" action="{{ route($page->status->value === 'published' ? 'admin.pages.unpublish' : 'admin.pages.publish', $page) }}"
                :action="statuses['{{ $page->id }}'] === 'published' ? @js(route('admin.pages.unpublish', $page)) : @js(route('admin.pages.publish', $page))"
                @submit.prevent="changeStatus($el.action, 'POST', ['{{ $page->id }}'])">
                @csrf
                <button type="submit" @dragstart.stop.prevent :disabled="statusBusy" :aria-busy="pendingIds.includes('{{ $page->id }}')" class="page-status-control inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full align-middle focus-visible:outline-none focus-visible:ring-4 disabled:cursor-wait disabled:opacity-50"
                    :class="statuses['{{ $page->id }}'] === 'published' ? 'text-success-500 hover:bg-success-50 hover:text-success-600 focus-visible:ring-success-500/20 dark:hover:bg-success-500/10' : 'text-error-500 hover:bg-error-50 hover:text-error-600 focus-visible:ring-error-500/20 dark:hover:bg-error-500/10'"
                    :title="pendingIds.includes('{{ $page->id }}') ? 'Updating status…' : (statuses['{{ $page->id }}'] === 'published' ? 'Unpublish page' : 'Publish page')"
                    :aria-label="(statuses['{{ $page->id }}'] === 'published' ? 'Unpublish ' : 'Publish ') + @js($page->title)">
                    <x-common.menu-icon name="activate" x-show="statuses['{{ $page->id }}'] === 'published'" style="{{ $page->status->value === 'published' ? '' : 'display: none' }}" class="page-status-icon h-7 w-7" />
                    <x-common.menu-icon name="deactivate" x-show="statuses['{{ $page->id }}'] !== 'published'" style="{{ $page->status->value === 'published' ? 'display: none' : '' }}" class="page-status-icon h-7 w-7" />
                </button>
            </form>
        @else
            <span title="{{ ucfirst($page->status->value) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full align-middle {{ $page->status->value === 'published' ? 'text-success-500' : 'text-error-500' }}">
                <x-common.menu-icon :name="$page->status->value === 'published' ? 'activate' : 'deactivate'" class="page-status-icon h-7 w-7" />
                <span class="sr-only">{{ ucfirst($page->status->value) }}</span>
            </span>
        @endcan
    </td>
    <td class="w-12 px-2 py-4 text-center" @mousedown.stop><x-common.table-checkbox value="{{ $page->id }}" x-model="selected" aria-label="Select {{ $page->title }}" @dragstart.stop.prevent /></td>
    <td class="px-3 py-4"><div class="font-medium text-gray-800 dark:text-white">{{ str_repeat('-- ', (int) ($page->tree_level ?? 0)) }}{{ $page->title }}</div></td>
    <td class="px-3 py-4"><div class="flex items-center justify-end gap-3">
        <time datetime="{{ $page->created_at?->toDateString() }}" class="whitespace-nowrap text-sm text-gray-500">{{ $page->created_at?->format('M d, Y') }}</time>
        @can('pages.edit')<a href="{{ route('admin.pages.edit', $page) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan
        @can('pages.delete')<form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page and its children?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20" title="Delete page"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan
    </div></td>
</tr>
