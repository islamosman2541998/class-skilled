<?php

namespace App\Http\Middleware;

use App\Support\VisitorSource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers how a visitor first arrived (landing page, external referrer, UTM)
 * for 30 days, so requests sent later can be attributed.
 */
class CaptureFirstTouch
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->hasCookie(VisitorSource::COOKIE)) {
            Cookie::queue(VisitorSource::COOKIE, (string) json_encode(VisitorSource::firstTouch($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 60 * 24 * 30);
        }

        return $next($request);
    }
}
