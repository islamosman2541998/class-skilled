<?php

namespace App\Http\Middleware;

use App\Settings\TrackingSettings;
use App\Support\Turnstile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Security headers for every response. The CSP allows third parties only for
 * integrations in use: GA4/GTM/Meta (Settings → Tracking, production only),
 * Turnstile (Settings → Forms), YouTube/Vimeo embeds and Google Maps.
 * Livewire + Alpine need inline scripts and eval.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
        ];

        // Local `npm run dev` only: Vite may listen on an IPv6 literal ([::1]) that CSP source
        // syntax cannot express, so the policy is sent only for built assets (always in production).
        if (! Vite::isRunningHot()) {
            $headers['Content-Security-Policy'] = $this->csp();
        }

        if (app()->isProduction() && $request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    public function csp(): string
    {
        $script = ["'self'", "'unsafe-inline'", "'unsafe-eval'"];
        $connect = ["'self'"];
        $img = ["'self'", 'data:', 'blob:', 'https://i.ytimg.com', 'https://i.vimeocdn.com'];
        $frame = ["'self'", 'https://www.youtube-nocookie.com', 'https://www.youtube.com', 'https://player.vimeo.com', 'https://www.google.com', 'https://maps.google.com'];

        if (Turnstile::enabled()) {
            $script[] = 'https://challenges.cloudflare.com';
            $frame[] = 'https://challenges.cloudflare.com';
            $connect[] = 'https://challenges.cloudflare.com';
        }

        if (app()->isProduction()) {
            try {
                $tracking = app(TrackingSettings::class);

                if ($tracking->ga4_id || $tracking->gtm_id) {
                    array_push($script, 'https://www.googletagmanager.com', 'https://www.google-analytics.com');
                    array_push($connect, 'https://www.google-analytics.com', 'https://*.google-analytics.com', 'https://*.analytics.google.com', 'https://www.googletagmanager.com');
                    array_push($img, 'https://www.google-analytics.com', 'https://www.googletagmanager.com');
                    $frame[] = 'https://www.googletagmanager.com';
                }

                if ($tracking->meta_pixel_id) {
                    $script[] = 'https://connect.facebook.net';
                    $connect[] = 'https://www.facebook.com';
                    $img[] = 'https://www.facebook.com';
                }
            } catch (Throwable) {
                // Settings unavailable (e.g. during install): keep the strict defaults.
            }
        }

        // Local development with `npm run dev`.
        if (Vite::isRunningHot()) {
            $origin = rtrim((string) file_get_contents(public_path('hot')));
            $script[] = $origin;
            $connect[] = $origin;
            $connect[] = str_replace(['http://', 'https://'], ['ws://', 'wss://'], $origin);
        }

        $style = ["'self'", "'unsafe-inline'"];

        if (Vite::isRunningHot()) {
            $style[] = rtrim((string) file_get_contents(public_path('hot')));
        }

        return implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', array_unique($script)),
            'style-src '.implode(' ', $style),
            'img-src '.implode(' ', array_unique($img)),
            "font-src 'self' data:",
            'connect-src '.implode(' ', array_unique($connect)),
            'frame-src '.implode(' ', array_unique($frame)),
            "media-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
