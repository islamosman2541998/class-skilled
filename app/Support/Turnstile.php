<?php

namespace App\Support;

use App\Settings\FormSettings;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Optional Cloudflare Turnstile: active only when both keys are set in Settings → Forms.
 */
class Turnstile
{
    public static function enabled(): bool
    {
        $settings = app(FormSettings::class);

        return filled($settings->turnstile_site_key) && filled($settings->turnstile_secret);
    }

    public static function siteKey(): ?string
    {
        return static::enabled() ? app(FormSettings::class)->turnstile_site_key : null;
    }

    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (! static::enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => app(FormSettings::class)->turnstile_secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return (bool) $response->json('success');
        } catch (Throwable) {
            // Never lose a real request because the verification service is unreachable.
            report('Turnstile verification failed: network error');

            return true;
        }
    }
}
