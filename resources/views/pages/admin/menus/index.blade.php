@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Public Menus">
    <x-slot:actions><a href="{{ route('admin.menus.create') }}" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">Add menu item</a></x-slot:actions>
</x-common.page-breadcrumb>
<x-common.component-card title="Dynamic menus" desc="Manage separate Header Menu and Footer Menu positions. Additional database-defined menu locations appear below.">
    @php
        $menuSections = [
            ['title' => 'Header Menu', 'description' => 'Primary navigation shown at the top of the public site.', 'location' => 'header', 'menus' => $headerMenus],
            ['title' => 'Footer Menu', 'description' => 'Navigation links shown in the public site footer.', 'location' => 'footer', 'menus' => $footerMenus],
            ['title' => 'Dynamic Menus', 'description' => 'Other menu positions created for dynamic navigation areas.', 'location' => null, 'menus' => $dynamicMenus],
        ];
        if ($menuLocation !== null) {
            $menuSections = array_values(array_filter($menuSections, fn (array $section): bool => $section['location'] === $menuLocation));
        }
    @endphp

    <div class="space-y-8">
        @foreach($menuSections as $section)
            <section>
                <div class="flex flex-col gap-1 border-b border-gray-100 pb-3 dark:border-gray-800 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white">{{ $section['title'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $section['description'] }}</p>
                    </div>
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $section['menus']->sum(fn ($menu) => $menu->items->count()) }} items</span>
                </div>

                <div class="mt-4 space-y-5">
                    @forelse($section['menus'] as $menu)
                        <div>
                            <div class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span>{{ $menu->name }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-normal text-gray-500 dark:bg-gray-800">{{ $menu->location }}</span>
                            </div>
                            @if($availablePages->get($menu->id, collect())->isNotEmpty())
                                <form method="POST" action="{{ route('admin.menus.assign') }}" class="mb-4 rounded-lg border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                                    @csrf
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                                        <label class="block min-w-0 flex-1">
                                            <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Assign pages</span>
                                            <select name="page_ids[]" multiple size="4" required class="block min-h-28 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                @foreach($availablePages->get($menu->id, collect()) as $page)
                                                    <option value="{{ $page->id }}">{{ str_repeat('-- ', substr_count($page->path, '/')) }}{{ $page->title }} ({{ $page->path }})</option>
                                                @endforeach
                                            </select>
                                            <span class="mt-1 block text-xs text-gray-500">Hold Ctrl or Command to select multiple pages.</span>
                                        </label>
                                        <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">Assign selected</button>
                                    </div>
                                </form>
                            @endif
                            <ul class="space-y-2">
                                @forelse($menu->items as $item)
                                    <li class="group flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm transition duration-200 hover:border-brand-300 hover:bg-brand-50/40 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-500/50 dark:hover:bg-brand-500/5" onclick="if (!event.target.closest('a,button,form')) window.location.href='{{ route('admin.menus.edit', $item) }}'" title="Open {{ $item->label }} for editing">
                                        <a href="{{ route('admin.menus.edit', $item) }}" class="min-w-0 flex-1 text-gray-800 dark:text-white">
                                            <span class="font-medium transition group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $item->label }}</span>
                                            <span class="text-gray-500">- {{ $item->page?->path ?? $item->external_url }}</span>
                                        </a>
                                        <span class="ml-4 flex shrink-0 items-center gap-2">
                                            <a class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:text-brand-400 dark:hover:bg-brand-500/10" href="{{ route('admin.menus.edit', $item) }}">Edit</a>
                                            <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" onclick="event.stopPropagation()" onsubmit="return confirm('Delete this menu item?')">@csrf @method('DELETE')<button class="inline-flex items-center rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 focus:outline-none focus:ring-2 focus:ring-error-500/30 dark:bg-error-500/10 dark:hover:bg-error-500/20" type="submit">Delete</button></form>
                                        </span>
                                    </li>
                                @empty
                                    <li class="rounded-lg border border-dashed border-gray-200 px-4 py-4 text-sm text-gray-500 dark:border-gray-800">No items in this menu.</li>
                                @endforelse
                            </ul>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-200 px-4 py-5 text-sm text-gray-500 dark:border-gray-800">No menu position configured.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-common.component-card>
@endsection
