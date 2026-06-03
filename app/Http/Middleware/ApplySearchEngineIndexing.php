<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySearchEngineIndexing
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! Seo::shouldIndex($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow', false);
        }

        return $response;
    }
}
