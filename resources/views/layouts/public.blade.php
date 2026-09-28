@php($page = $page ?? null)
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($settings['site_name'] ?? config('app.name')) }}</title>
    @if(!empty($page?->meta_description))<meta name="description" content="{{ $page->meta_description }}">@endif
    @if($page?->socialMedia)
        <meta property="og:title" content="{{ $title ?? $page->title }}">
        @if($page->meta_description)<meta property="og:description" content="{{ $page->meta_description }}">@endif
        <meta property="og:image" content="{{ $page->socialMedia->url() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3 font-semibold">
                @if(!empty($settings['logo_url']))<img src="{{ $settings['logo_url'] }}" alt="{{ $settings['site_name'] ?? 'Site logo' }}" class="h-12 w-auto">@endif
                <span>{{ $settings['site_name'] ?? config('app.name') }}</span>
            </a>
            @if($mainMenu?->items?->isNotEmpty())
                <nav aria-label="Main navigation"><ul class="flex flex-wrap gap-4 text-sm">
                    @foreach($mainMenu->items as $item)
                        @include('public.partials.menu-item', ['item' => $item])
                    @endforeach
                </ul></nav>
            @endif
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-10">@yield('content')</main>
    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-6 py-8 text-sm text-gray-600 sm:flex-row sm:items-start sm:justify-between">
            <div>{{ $settings['footer_text'] ?? $settings['office_name'] ?? '' }}</div>
            @if($footerMenu?->items?->isNotEmpty())
                <nav aria-label="Footer navigation">
                    <ul class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach($footerMenu->items as $item)
                            @include('public.partials.menu-item', ['item' => $item])
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </footer>
</body>
</html>
