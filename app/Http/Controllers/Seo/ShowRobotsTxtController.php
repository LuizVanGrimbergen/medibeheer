<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Support\Seo;
use Illuminate\Http\Response;

class ShowRobotsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
        ];

        foreach (Seo::robotsDisallowPaths() as $path) {
            $lines[] = "Disallow: {$path}";
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('seo.sitemap', absolute: true);

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
