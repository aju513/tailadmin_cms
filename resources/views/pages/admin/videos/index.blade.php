@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Videos">
    <x-slot:actions>
        @can('videos.create')
            <a href="{{ route('admin.videos.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Add Video</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Manage videos">
    <form method="GET" action="{{ route('admin.videos.index') }}" class="mb-6 flex items-end gap-3">
        <div class="flex-1"><x-form.input name="search" label="Search" :value="request('search')" placeholder="Search by title" /></div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Video</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($records as $record)
                <tr class="text-sm text-gray-600 dark:text-gray-300">
                    <td class="px-4 py-4"><div class="flex items-center gap-3">
                        @if($record->coverMedia)<img src="{{ $record->coverMedia->url() }}" alt="" class="h-12 w-16 rounded-lg object-cover" />@endif
                        <div><div class="font-medium text-gray-800 dark:text-white/90">{{ $record->title }}</div></div>
                    </div></td>
                    <td class="px-4 py-4"><a href="{{ $record->video_url }}" target="_blank" rel="noopener noreferrer" class="font-medium text-brand-500 hover:underline">Open video</a></td>
                    <td class="px-4 py-4">
                        <x-ui.badge :color="$record->status->value === 'published' ? 'success' : 'warning'">{{ ucfirst($record->status->value) }}</x-ui.badge>
                        @if($record->status->value === 'published' && $record->published_at?->isFuture())
                            <div class="mt-1 text-xs text-gray-500">Scheduled {{ $record->published_at->format('d M Y H:i') }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-4"><div class="flex justify-end gap-2">
                        @can('videos.edit')
                            <a href="{{ route('admin.videos.edit', $record) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">Edit</a>
                        @endcan
                        @can('videos.delete')
                            @if($record->status->value !== 'published' || auth()->user()->can('videos.publish'))
                            <form method="POST" action="{{ route('admin.videos.destroy', $record) }}" onsubmit="return confirm('Delete this video? Uploaded files will remain in the Media Library.')">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">Delete</x-ui.button>
                            </form>
                            @endif
                        @endcan
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No videos found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $records->links() }}</div>
</x-common.component-card>
@endsection
