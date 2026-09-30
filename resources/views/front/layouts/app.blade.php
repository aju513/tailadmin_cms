<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('front.partials.metadata')
    @vite(['resources/front/css/app.css','resources/front/js/app.js'])
</head>
<body>
    <main id="main">
    @include('front.partials.header')
    @yield('content')
    @include('front.partials.footer')
    </main>
</body>
</html>
