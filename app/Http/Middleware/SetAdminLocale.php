<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard UI language: the signed-in user's preference, else the session
 * (login screen toggle), else Arabic.
 */
class SetAdminLocale
{
    public const LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->getAttribute('locale')
            ?? ($request->hasSession() ? $request->session()->get('admin_locale') : null)
            ?? 'ar';

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'ar');

        return $next($request);
    }
}
