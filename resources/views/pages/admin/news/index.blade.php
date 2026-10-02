@extends('layouts.app')

@section('content')
<div x-data="pageManager(@js($items->mapWithKeys(fn ($record) => [(string) $record->id => $record->status->value])->all()), 'news')">
<x-common.page-breadcrumb pageTitle="News">
    <x-slot:actions>
        @can('news.publish')
            <form method="POST" action="{{ route('admin.news.bulk-status') }}" @submit.prevent="changeStatus($el.action, 'PATCH', [...selected], $event.submitter?.value)" class="flex items-center gap-2">
                @csrf @method('PATCH')
                <template x-for="newsId in selected" :key="`status-${newsId}`"><input type="hidden" name="news[]" :value="newsId"></template>
                <button name="status" value="published" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="activate" class="h-4 w-4" />Publish</button>
                <button name="status" value="draft" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:cursor-not-allowed disabled:opacity-40"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Unpublish</button>
            </form>
        @endcan
        @can('news.create')
            <a href="{{ route('admin.news.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add News</a>
        @endcan
        @can('news.delete')
            <form method="POST" action="{{ route('admin.news.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected news articles?')" class="flex items-center">
                @csrf @method('DELETE')
                <template x-for="newsId in selected" :key="`delete-${newsId}`"><input type="hidden" name="news[]" :value="newsId"></template>
                <button type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Bulk delete</button>
            </form>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
    <x-common.table-status-feedback />

<x-common.component-card title="News manager" desc="Drag rows to reorder news. Use the selection controls for bulk actions.">
    <form method="GET" action="{{ route('admin.news.index') }}" class="mb-6 grid gap-3 sm:grid-cols-[1fr_180px_auto]"><input name="search" value="{{ request('search') }}" placeholder="Search title" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white"><select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option value="">All statuses</option><option value="draft" @selected(request('status') === 'draft')>Draft</option><option value="published" @selected(request('status') === 'published')>Published</option></select><button class="rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-white">Filter</button></form>
    <div x-data="newsOrdering('{{ route('admin.news.order') }}')">
        <div x-show="message" x-text="message" class="mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700" x-cloak></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800"><thead><tr class="text-left text-xs uppercase text-gray-500"><th scope="col" aria-label="Reorder news" class="w-10 px-2 py-3"></th><th class="w-16 px-2 py-3 text-center">Status</th><th scope="col" class="w-12 px-2 py-3 text-center"><x-common.table-select-all aria-label="Select all displayed news" /></th><th class="px-3 py-3">Title</th><th class="px-3 py-3 text-right">Created date / Actions</th></tr></thead><tbody x-ref="rows" class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($items as $item)
                <tr data-news-id="{{ $item->id }}" draggable="true" @dragstart="start($event)" @dragover="over($event)" @drop="drop($event)" @dragend="end()" @can('news.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.news.edit', $item) }}'" @endcan class="@can('news.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan bg-white transition dark:bg-transparent">
                    <td class="w-10 px-2 py-4 text-center text-gray-400"><span class="inline-flex cursor-grab items-center justify-center" title="Drag to reorder" aria-label="Drag to reorder"><i class="bi bi-arrows-move" aria-hidden="true"></i></span></td>
                    <td class="w-16 px-2 py-4 text-center" @mousedown.stop><x-common.table-status :id="$item->id" :status="$item->status->value" :label="$item->title" permission="news.publish" :url="route('admin.news.bulk-status')" selection-key="news" active-value="published" inactive-value="draft" /></td>
                    <td class="w-12 px-2 py-4 text-center" @mousedown.stop><x-common.table-checkbox value="{{ $item->id }}" x-model="selected" aria-label="Select {{ $item->title }}" @dragstart.stop.prevent /></td>
                    <td class="px-3 py-4"><div class="flex items-center gap-3">@if($item->thumbnailMedia)<img src="{{ $item->thumbnailMedia->url() }}" alt="" class="h-10 w-14 rounded-lg object-cover">@endif<div class="font-medium text-gray-800 dark:text-white">{{ $item->title }}@if($item->featured)<span class="ml-2 text-xs font-normal text-gray-500">Featured</span>@endif</div></div></td>
                    <td class="px-3 py-4"><div class="flex items-center justify-end gap-3"><time datetime="{{ $item->created_at?->toDateString() }}" class="whitespace-nowrap text-sm text-gray-500">{{ $item->created_at?->format('M d, Y') }}</time>@can('news.show')<a href="{{ route('admin.news.show', $item) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.04]"><x-common.menu-icon name="view" class="h-4 w-4" />View</a>@endcan @can('news.edit')<a href="{{ route('admin.news.edit', $item) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan @can('news.delete')<form method="POST" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('Delete this news article?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan</div></td>
                </tr>
            @empty<tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No news articles found.</td></tr>@endforelse
        </tbody></table></div>
    </div>
    {{ $items->links() }}
</x-common.component-card>
</div>
@endsection

@push('scripts')
<script>
window.newsOrdering = (url) => ({
    dragging: null, message: '',
    start(event) { this.dragging = event.currentTarget; event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', this.dragging.dataset.newsId); this.dragging.classList.add('opacity-50'); },
    over(event) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; },
    drop(event) { event.preventDefault(); const target = event.currentTarget; if (!this.dragging || this.dragging === target) return; const rect = target.getBoundingClientRect(); const after = event.clientY > rect.top + rect.height / 2; target.parentNode.insertBefore(this.dragging, after ? target.nextSibling : target); this.persist(); },
    end() { this.dragging?.classList.remove('opacity-50'); this.dragging = null; },
    persist() { const news = [...this.$refs.rows.querySelectorAll('[data-news-id]')].map(row => row.dataset.newsId); fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ news }) }).then(response => { if (!response.ok) throw new Error(); return response; }).then(() => { this.message = 'News order updated.'; setTimeout(() => this.message = '', 2500); }).catch(() => { this.message = 'Unable to update news order.'; }); }
});
</script>
@endpush
