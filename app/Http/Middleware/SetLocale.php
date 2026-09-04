<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported  = config('localization.supported_locales', ['en', 'es']);
        $default    = config('localization.default_locale', 'en');
        $sessionKey = config('localization.session_key', 'locale');
        $cookieName = config('localization.cookie_name', 'parcia_locale');

        $locale = $request->session()->get($sessionKey)
            ?? $request->cookie($cookieName)
            ?? $default;

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        if (! $request->session()->has($sessionKey)) {
            $request->session()->put($sessionKey, $locale);
        }

        return $next($request);
    }
}
