<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2f6fae">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title') · {{ config('app.name') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="/Atkinson_Hyperlegible/AtkinsonHyperlegible-Regular.ttf" as="font" type="font/ttf" crossorigin>

    @vite(['resources/css/app.css'])

    @stack('head')
</head>

<body class="min-h-dvh bg-bg font-sans antialiased">
    <main
        class="flex min-h-dvh items-center justify-center px-4 py-8 pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)] sm:px-6"
        role="main"
    >
        <div class="mx-auto w-full max-w-xl text-center">
            <div class="mb-6 flex justify-center">
                <img
                    src="/favicon.svg"
                    alt="{{ config('app.name') }}"
                    class="h-20 w-auto sm:h-24"
                >
            </div>

            <p class="text-primary text-sm font-semibold tracking-wide uppercase">
                @yield('code')
            </p>

            <h1 class="text-text-heading mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                @yield('heading')
            </h1>

            <p
                id="error-message"
                class="text-text-muted mx-auto mt-3 max-w-md text-base leading-relaxed sm:text-lg"
            >
                @yield('message')
            </p>

            @hasSection('details')
                <div class="mt-4">
                    @yield('details')
                </div>
            @endif

            <div class="mt-8 flex justify-center">
                <a
                    href="{{ route('home') }}"
                    class="bg-primary inline-flex h-11 items-center justify-center rounded-xl px-8 text-base font-semibold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-focus/20"
                >
                    {{ __('errors.back_home') }}
                </a>
            </div>
        </div>
    </main>

    @stack('scripts')
</body>

</html>
