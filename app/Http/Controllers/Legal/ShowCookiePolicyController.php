<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowCookiePolicyController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Legal/Cookies/Index', LegalPageProps::forInertia($request));
    }
}
