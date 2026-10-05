<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" data-translation-mode="{{ config('frontend.translation.mode') }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('front.partials.metadata')
    @include('front.partials.assets')
    @stack('styles')
</head>
<body>
    <div id="main">
        @include(config('frontend.layout.header'))
        <p data-language-feedback role="status" class="container my-4 text-primary" hidden></p>
        <main id="content">
            @yield('content')
        </main>
        @include(config('frontend.layout.footer'))
    </div>
    @if(config('frontend.translation.mode') === 'gtranslate')
        <div class="gtranslate_wrapper notranslate" hidden aria-hidden="true"></div>
        <script>window.gtranslateSettings = { default_language: 'en', languages: ['en', 'ne'], wrapper_selector: '.gtranslate_wrapper' };</script>
        <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
    @endif
    @stack('scripts')
</body>
</html>
