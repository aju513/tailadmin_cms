@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Resource Categories">
    <x-slot:actions>
        @can('resource-categories.create')
            <a href="{{ route('admin.resource-categories.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Add Resource Category</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Manage resource categories">
    <form method="GET" action="{{ route('admin.resource-categories.index') }}" class="mb-6 flex items-end gap-3">
        <div class="flex-1"><x-form.input name="search" label="Search" :value="request('search')" placeholder="Search by name" /></div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($records as $record)
                <tr class="text-sm text-gray-600 dark:text-gray-300">
                    <td class="px-4 py-4"><div class="flex items-center gap-3">
                        <div><div class="font-medium text-gray-800 dark:text-white/90">{{ $record->name }}</div><div class="mt-1 text-xs text-gray-500">{{ $record->slug }}</div></div>
                    </div></td>
                    <td class="px-4 py-4"><div class="flex justify-end gap-2">
                        @can('resource-categories.edit')
                            <a href="{{ route('admin.resource-categories.edit', $record) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">Edit</a>
                        @endcan
                        @can('resource-categories.delete')
                            <form method="POST" action="{{ route('admin.resource-categories.destroy', $record) }}" onsubmit="return confirm('Delete this resource category?')">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
                            </form>
                        @endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="2" class="px-4 py-10 text-center text-sm text-gray-500">No resource categories found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $records->links() }}</div>
</x-common.component-card>
@endsection
