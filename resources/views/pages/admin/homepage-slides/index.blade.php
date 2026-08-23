@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Homepage Slides">
    <x-slot:actions>
        @can('homepage-slides.create')<a href="{{ route('admin.homepage-slides.create') }}" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">Create slide</a>@endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Homepage slides" desc="Manage the public homepage hero content.">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-4 py-3">Slide</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($slides as $slide)
                    <tr @can('homepage-slides.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.homepage-slides.edit', $slide) }}'" title="Open {{ $slide->title }} for editing" @endcan class="group transition @can('homepage-slides.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan">
                        <td class="px-4 py-4 font-medium text-gray-800 dark:text-white">{{ $slide->title }}</td>
                        <td class="px-4 py-4 text-sm">{{ ucfirst($slide->status->value) }}</td>
                        <td class="px-4 py-4"><div class="flex justify-end gap-2">
                            <a class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-brand-400 dark:hover:bg-brand-500/10" href="{{ route('admin.homepage-slides.edit', $slide) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.homepage-slides.destroy', $slide) }}" onsubmit="return confirm('Delete this slide?')">@csrf @method('DELETE')<button class="inline-flex rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20" type="submit">Delete</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-sm text-gray-500">No slides found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $slides->links() }}
</x-common.component-card>
@endsection
