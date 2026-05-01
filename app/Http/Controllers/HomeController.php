<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    public function __invoke(): RedirectResponse
    {
        return redirect()->route('login');
    }
}
