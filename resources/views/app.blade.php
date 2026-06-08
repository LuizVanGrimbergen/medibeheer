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

    @isset($documentMeta)
        @php
            $siteName = config('app.name', 'Medibeheer');
            $pageTitle = $documentMeta['title'];
            $fullTitle = "{$pageTitle} - {$siteName}";
            $description = $documentMeta['description'];
            $canonicalUrl = $documentCanonicalUrl;
            $ogImageUrl = $documentOgImageUrl;
            $ogLocale = config('seo.locale', 'nl_BE');
        @endphp
        <title>{{ $fullTitle }}</title>
        <meta name="description" content="{{ $description }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="{{ $ogLocale }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $fullTitle }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $ogImageUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $fullTitle }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $ogImageUrl }}">
    @else
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @endisset
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
    @isset($crawlableHomeContent)
        @include('seo.crawlable-home')
    @endisset

    @inertia
</body>

</html>
