<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Seo
{
    /** @return list<string> */
    public static function indexableRouteNames(): array
    {
        /** @var list<string> $names */
        $names = config('seo.indexable_route_names', []);

        return $names;
    }

    /** @return list<string> */
    public static function sitemapRouteNames(): array
    {
        /** @var list<string> $names */
        $names = config('seo.sitemap_route_names', []);

        return $names;
    }

    public static function shouldIndex(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        return in_array($routeName, self::indexableRouteNames(), true);
    }

    public static function canonicalUrl(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if (
            $routeName !== null
            && in_array($routeName, self::indexableRouteNames(), true)
            && $request->route()?->parameterNames() === []
        ) {
            return route($routeName, absolute: true);
        }

        return $request->url();
    }

    public static function ogImageUrl(): string
    {
        /** @var string $path */
        $path = config('seo.og_image', '/images/medibeheer-pwa-512.png');

        return url($path);
    }

    public static function openGraphLocale(): string
    {
        /** @var string $locale */
        $locale = config('seo.locale', 'nl_BE');

        return $locale;
    }

    /** @return array<string, mixed> */
    public static function homeStructuredData(Request $request): array
    {
        /** @var string $description */
        $description = config('seo.description');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => config('app.name'),
            'applicationCategory' => 'HealthApplication',
            'operatingSystem' => 'Web',
            'description' => $description,
            'url' => self::canonicalUrl($request),
            'inLanguage' => 'nl',
        ];
    }

    /** @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}> */
    public static function sitemapEntries(): array
    {
        $entries = [];

        /** @var array<string, array{changefreq: string, priority: string}> $priorities */
        $priorities = config('seo.sitemap_priorities', []);

        foreach (self::sitemapRouteNames() as $routeName) {
            if (! Route::has($routeName)) {
                continue;
            }

            $priority = $priorities[$routeName] ?? ['changefreq' => 'monthly', 'priority' => '0.5'];

            $entries[] = [
                'loc' => route($routeName, absolute: true),
                'lastmod' => now()->toAtomString(),
                'changefreq' => $priority['changefreq'],
                'priority' => $priority['priority'],
            ];
        }

        return $entries;
    }

    /** @return list<string> */
    public static function robotsDisallowPaths(): array
    {
        /** @var list<string> $paths */
        $paths = config('seo.robots_disallow_paths', []);

        return $paths;
    }
}
