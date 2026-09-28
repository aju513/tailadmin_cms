@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Public Menus" />
<x-common.component-card title="Menu positions" desc="Assign pages to the main and footer navigation. Child pages appear under their assigned parent.">
    @php
        $menuSections = [
            ['title' => 'Main Menu', 'location' => 'header', 'menus' => $headerMenus],
            ['title' => 'Footer Menu', 'location' => 'footer', 'menus' => $footerMenus],
            ['title' => 'Dynamic Menus', 'location' => null, 'menus' => $dynamicMenus],
        ];
        if ($menuLocation !== null) {
            $menuSections = array_values(array_filter($menuSections, fn (array $section): bool => $section['location'] === $menuLocation));
        }
    @endphp
    <div class="space-y-8">
        @foreach($menuSections as $section)
            <section>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-800 dark:text-white">{{ $section['title'] }}</h3>
                    <span class="text-xs text-gray-500">{{ $section['menus']->sum(fn ($menu) => $menu->items->count()) }} items</span>
                </div>
                <div class="mt-4 space-y-6">
                    @forelse($section['menus'] as $menu)
                        <div>
                            @if($section['location'] === null)<h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $menu->name }}</h4>@endif
                            <form method="POST" action="{{ route('admin.menus.assign') }}" class="mb-4 rounded-lg border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                                    @csrf
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                        <div class="min-w-0 flex-1">
                                            <x-form.multiselect name="page_ids[]" :id="'page-ids-'.$menu->id" label="Assign pages" :options="$availablePages->get($menu->id, collect())->map(fn ($page) => ['value' => $page->id, 'label' => str_repeat('-- ', substr_count($page->path, '/')).$page->title.' ('.$page->path.')'])->all()" placeholder="Search and select pages" help="Select one or more pages. Parent and child pages keep their hierarchy." />
                                        </div>
                                        <button type="submit" @disabled($availablePages->get($menu->id, collect())->isEmpty()) class="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Assign Menu</button>
                                    </div>
                            </form>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900"><tr><th class="px-4 py-3">Menu title</th><th class="px-4 py-3">Page / URL</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($menu->items->filter(fn ($item) => ! $item->parent_id || ! $menu->items->contains('id', $item->parent_id)) as $item)
                                            @include('pages.admin.menus._item-row', ['item' => $item, 'allItems' => $menu->items, 'depth' => 0])
                                        @empty
                                            <tr><td colspan="3" class="px-4 py-5 text-gray-500">No items in this menu.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-200 px-4 py-5 text-sm text-gray-500 dark:border-gray-800">No menu position configured.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-common.component-card>
@endsection
