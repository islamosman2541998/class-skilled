<?php

namespace App\Http\Middleware;

use App\Settings\MaintenanceSettings;
use App\Support\Translate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard-controlled maintenance page for the public site (no `artisan down`):
 * guests see a branded 503 page; signed-in users and allowed IPs browse normally.
 */
class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(MaintenanceSettings::class);

        if (! $settings->enabled || $request->user() !== null || in_array($request->ip(), $settings->allowed_ips, true)) {
            return $next($request);
        }

        return response()->view('site.maintenance', [
            'message' => Translate::pick($settings->message),
        ], 503)->header('Retry-After', '3600');
    }
}
