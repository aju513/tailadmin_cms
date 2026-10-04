@extends('admin.layouts.app')

@section('content')
@php
    $menuSections = [
        ['title' => 'Main Menu', 'location' => 'header', 'description' => 'Manage the navigation displayed in the website header.', 'menus' => $headerMenus],
        ['title' => 'Footer Menu', 'location' => 'footer', 'description' => 'Organize the quick links displayed in the website footer.', 'menus' => $footerMenus],
        ['title' => 'Important Links Menu', 'location' => 'important_links', 'description' => 'Manage external websites displayed under Important Links in the footer.', 'menus' => $importantLinksMenus],
        ['title' => 'Dynamic Menus', 'location' => null, 'description' => 'Manage additional website menu positions.', 'menus' => $dynamicMenus],
    ];
    if ($menuLocation !== null) {
        $menuSections = array_values(array_filter($menuSections, fn (array $section): bool => $section['location'] === $menuLocation));
    }
    $pageTitle = $menuLocation === null ? 'Public Menus' : $menuSections[0]['title'];
@endphp

<x-common.page-breadcrumb :pageTitle="$pageTitle" />

<div class="space-y-10">
    @foreach($menuSections as $section)
        <section aria-label="{{ $section['title'] }} Manager" class="space-y-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-brand-100 bg-brand-50 text-brand-500 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-400">
                        <x-common.menu-icon name="menus" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $section['title'] }} Manager</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $section['description'] }}</p>
                    </div>
                </div>
                <span class="inline-flex w-fit shrink-0 items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">{{ $section['menus']->sum(fn ($menu) => $menu->items->count()) }} items</span>
            </div>

            <div class="space-y-8">
                @forelse($section['menus'] as $menu)
                    @php
                        $hasLinkErrors = (int) old('menu_id') === $menu->id && ($errors->has('label') || $errors->has('external_url') || $errors->has('parent_id'));
                        $initialPanel = $menu->isImportantLinks() || $hasLinkErrors ? 'link' : 'pages';
                    @endphp
                    <div x-data="menuManager(@js(route('admin.menus.order')), {{ $menu->id }}, @js($initialPanel))" class="min-w-0 space-y-4">
                        @if($section['location'] === null)
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $menu->name }}</h3>
                        @endif
                        <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                            @include('admin.pages.menus._composer', ['menu' => $menu])
                            @include('admin.pages.menus._structure', ['menu' => $menu])
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-gray-800"><x-common.menu-icon name="menus" /></span>
                        <p class="mt-4 text-sm font-medium text-gray-800 dark:text-white/90">No menu position configured</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Menu positions will appear here when they are available.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection

@push('scripts')
@include('admin.pages.menus._scripts')
@endpush
