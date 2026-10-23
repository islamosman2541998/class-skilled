<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('admin_locale', 'ar');
        app()->setLocale(in_array($locale, ['ar', 'en'], true) ? $locale : 'ar');

        return $next($request);
    }
}
