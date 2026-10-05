@extends('admin.layouts.app')

@section('content')
<div x-data="pageManager(@js($records->mapWithKeys(fn ($record) => [(string) $record->id => ($record->is_active ? '1' : '0')])->all()), 'categories')">
<x-common.page-breadcrumb pageTitle="Resource Categories">
    <x-slot:actions>
        @can('resource-categories.edit')<form method="POST" action="{{ route('admin.resource-categories.bulk-status') }}" @submit.prevent="changeStatus($el.action, 'PATCH', [...selected], $event.submitter?.value)" class="flex items-center gap-2">@csrf @method('PATCH')<template x-for="categoryId in selected" :key="`status-${categoryId}`"><input type="hidden" name="categories[]" :value="categoryId"></template><button name="status" value="1" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:opacity-40"><x-common.menu-icon name="activate" class="h-4 w-4" />Activate</button><button name="status" value="0" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:opacity-40"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Deactivate</button></form>@endcan
        @can('resource-categories.create')
            <a href="{{ route('admin.resource-categories.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add Category</a>
        @endcan
        @can('resource-categories.delete')<form method="POST" action="{{ route('admin.resource-categories.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected resource categories?')">@csrf @method('DELETE')<template x-for="categoryId in selected" :key="`delete-${categoryId}`"><input type="hidden" name="categories[]" :value="categoryId"></template><button type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:opacity-40 dark:bg-error-500/10"><x-common.menu-icon name="delete" class="h-4 w-4" />Bulk delete</button></form>@endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
    <x-common.table-status-feedback />
@include('admin.pages.categories._manager', ['module' => 'resource-categories', 'showSlug' => false])
</div>
@endsection
