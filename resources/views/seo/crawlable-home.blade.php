@php
    /** @var array{heading: string, features_heading: string, features: list<array{title: string, summary: string}>} $crawlableHomeContent */
    $siteName = config('app.name', 'Medibeheer');
    $description = config('seo.pages.home.description');
@endphp

<main class="sr-only" aria-label="{{ $siteName }}">
    <h1>{{ $crawlableHomeContent['heading'] }}</h1>
    <p>{{ $description }}</p>

    <h2>{{ $crawlableHomeContent['features_heading'] }}</h2>
    <ul>
        @foreach ($crawlableHomeContent['features'] as $feature)
            <li>
                <strong>{{ $feature['title'] }}</strong>
                – {{ $feature['summary'] }}
            </li>
        @endforeach
    </ul>

    <p>
        <a href="{{ route('login') }}">Inloggen</a>
        ·
        <a href="{{ route('register') }}">Registreren</a>
        ·
        <a href="{{ route('legal.privacy') }}">Privacybeleid</a>
    </p>
</main>

<noscript>
    <div class="mx-auto max-w-2xl px-4 py-8 text-slate-800 sm:px-6">
        <h1 class="text-3xl font-bold">{{ $crawlableHomeContent['heading'] }}</h1>
        <p class="mt-4 text-base leading-relaxed">{{ $description }}</p>

        <h2 class="mt-8 text-xl font-semibold">{{ $crawlableHomeContent['features_heading'] }}</h2>
        <ul class="mt-4 list-disc space-y-2 ps-6 text-base leading-relaxed">
            @foreach ($crawlableHomeContent['features'] as $feature)
                <li>
                    <strong>{{ $feature['title'] }}</strong>
                    – {{ $feature['summary'] }}
                </li>
            @endforeach
        </ul>

        <p class="mt-8 text-base">
            <a href="{{ route('login') }}" class="font-semibold text-sky-800 underline">Inloggen</a>
            ·
            <a href="{{ route('register') }}" class="font-semibold text-sky-800 underline">Registreren</a>
        </p>
    </div>
</noscript>
