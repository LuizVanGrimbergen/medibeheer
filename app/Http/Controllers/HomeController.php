<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            return redirect()->to($user->defaultAuthenticatedHomeUrl());
        }

        return Inertia::render('Guest/Home');
    }
}
