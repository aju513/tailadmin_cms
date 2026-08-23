@extends('layouts.app')

@section('content')
@php($canEdit = auth()->user()->can($resource.'.edit'))
<x-common.page-breadcrumb :pageTitle="$title">
    <x-slot:actions>
        @can($resource.'.create')
            <a href="{{ route('admin.'.$resource.'.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">Create {{ strtolower($resourceLabel) }}</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>

<x-common.component-card :title="$title" desc="Manage reusable content metadata.">
    <form method="GET" class="mb-5 flex gap-3">
        <input name="search" value="{{ request('search') }}" placeholder="Search {{ strtolower($resourceLabel) }}" class="h-11 flex-1 rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        <button class="rounded-lg border border-gray-300 px-4 text-sm transition hover:border-brand-500 hover:bg-brand-50 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($items as $item)
                    <tr @if($canEdit) onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.'.$resource.'.edit', $item) }}'" title="Open {{ $item->name }} for editing" @endif class="group transition {{ $canEdit ? 'cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5' : 'hover:bg-gray-50 dark:hover:bg-white/[0.02]' }}">
                        <td class="px-4 py-4 font-medium text-gray-800 dark:text-white">{{ $item->name }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $item->slug }}</td>
                        <td class="px-4 py-4 text-sm">{{ $item->status ? 'Active' : 'Inactive' }}</td>
                        <td class="px-4 py-4"><div class="flex justify-end gap-2">
                            @can($resource.'.edit')<a class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-brand-400 dark:hover:bg-brand-500/10" href="{{ route('admin.'.$resource.'.edit', $item) }}">Edit</a>@endcan
                            @can($resource.'.delete')<form method="POST" action="{{ route('admin.'.$resource.'.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="inline-flex rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20" type="submit">Delete</button></form>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</x-common.component-card>
@endsection
