<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Support\Seo;
use Illuminate\Http\Response;

class ShowSitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = Seo::sitemapEntries();

        $body = view('seo.sitemap', ['urls' => $urls])->render();

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
