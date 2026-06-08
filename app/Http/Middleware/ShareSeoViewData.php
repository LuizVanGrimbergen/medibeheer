<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareSeoViewData
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $documentMeta = Seo::documentMeta($request);

        if ($documentMeta !== null) {
            View::share([
                'documentMeta' => $documentMeta,
                'documentCanonicalUrl' => Seo::canonicalUrl($request),
                'documentOgImageUrl' => Seo::ogImageUrl(),
            ]);
        }

        if ($request->routeIs('home')) {
            View::share(
                'structuredDataJson',
                json_encode(
                    Seo::homeStructuredData($request),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
            );

            $crawlableHomeContent = Seo::crawlableHomeContent();

            if ($crawlableHomeContent !== null) {
                View::share('crawlableHomeContent', $crawlableHomeContent);
            }
        }

        return $next($request);
    }
}
