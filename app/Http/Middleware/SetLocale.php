<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale')
            ?? optional(Auth::user())->locale
            ?? config('app.locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
