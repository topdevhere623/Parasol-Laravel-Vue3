<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WebsiteThemeService;
use Illuminate\Http\Request;

class SignInController extends Controller
{
    public function signIn(Request $request, WebsiteThemeService $theme)
    {
        return view('sign-in', [
            'theme' => $theme,
        ]);
    }
}
