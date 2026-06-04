<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2f6fae">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Medibeheer') }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/images/medibeheer-pwa.png" type="image/png" sizes="192x192">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="/favicon.svg" as="image" type="image/svg+xml">
    <link rel="preload" href="/Atkinson_Hyperlegible/AtkinsonHyperlegible-Regular.ttf" as="font" type="font/ttf" crossorigin>

    @isset($structuredDataJson)
        <script type="application/ld+json">{!! $structuredDataJson !!}</script>
    @endisset

    <!-- Scripts -->
    @if (request()->routeIs('home', 'login', 'register', 'legal.*', 'password.*', 'verification.*'))
        @routes('public', json: true)
    @else
        @routes(json: true)
    @endif
    @vite('resources/js/app.ts')
    @inertiaHead
</head>

<body class="min-h-dvh bg-slate-50 font-sans antialiased">
    @inertia
</body>

</html>
