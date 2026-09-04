<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale     = $request->validate(['locale' => 'required|in:en,es'])['locale'];
        $sessionKey = config('localization.session_key', 'locale');
        $cookieName = config('localization.cookie_name', 'parcia_locale');
        $minutes    = config('localization.cookie_minutes', 525600);

        session()->put($sessionKey, $locale);

        return redirect()->back()->withCookie(
            cookie()->make($cookieName, $locale, $minutes, '/', null, $request->isSecure(), true, false, 'Lax')
        );
    }
}
