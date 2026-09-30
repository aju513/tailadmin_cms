@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Halls">
    <x-slot:actions>@can('halls.create')<a href="{{ route('admin.halls.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add Hall</a>@endcan</x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Hall manager">
    <form method="GET" action="{{ route('admin.halls.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-full sm:max-w-sm">
        <x-form.input name="search" label="Search halls" :value="request('search')" placeholder="Title, building, or location" />
        </div>
        <x-ui.button type="submit" variant="outline">Search</x-ui.button>
        @if (request()->filled('search'))<a href="{{ route('admin.halls.index') }}" class="py-3 text-sm text-brand-500 hover:underline">Reset</a>@endif
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-2 py-3">S.N.</th><th class="px-4 py-3">Hall</th><th class="px-4 py-3">Capacity</th><th class="px-4 py-3">Rental rate</th><th class="px-2 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($items as $hall)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-2 py-4 text-sm text-gray-500">{{ $items->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-4"><div class="flex items-center gap-3">@if ($hall->thumbnailMedia)<img src="{{ $hall->thumbnailMedia->url() }}" alt="" class="h-12 w-16 rounded-lg object-cover">@endif<div><p class="font-medium text-gray-800 dark:text-white">{{ $hall->title }}</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $hall->building_name }}{{ $hall->building_name && $hall->location ? ' · ' : '' }}{{ $hall->location }}</p></div></div></td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ number_format($hall->capacity) }} seats</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">@if ($hall->rental_rate !== null)NPR {{ number_format((float) $hall->rental_rate, 2) }}<p class="text-xs">{{ config('halls.rate_units.'.$hall->rate_unit) }}</p>@else Price on request @endif</td>
                        <td class="px-2 py-4"><x-ui.badge :color="$hall->status->value === 'published' ? 'success' : 'warning'">{{ $hall->published_at?->isFuture() ? 'Scheduled' : ucfirst($hall->status->value) }}</x-ui.badge>@if ($hall->availability_status !== 'available')<p class="mt-1 text-xs text-gray-500">{{ $hall->availability_status === 'maintenance' ? 'Maintenance' : 'Unavailable' }}</p>@endif</td>
                        <td class="px-4 py-4"><div class="flex justify-end gap-2">
                            @can('halls.show')<a href="{{ route('admin.halls.show', $hall) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs dark:border-gray-700 dark:text-gray-300">View</a>@endcan
                            @can('halls.edit')<a href="{{ route('admin.halls.edit', $hall) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 dark:border-gray-700 dark:text-brand-400">Edit</a>@endcan
                            @can('halls.delete')<form method="POST" action="{{ route('admin.halls.destroy', $hall) }}" onsubmit="return confirm('Delete this hall? Its media library images will remain available.')">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 dark:bg-error-500/10">Delete</button></form>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No halls found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</x-common.component-card>
@endsection
