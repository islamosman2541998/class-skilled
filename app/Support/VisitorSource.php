<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Where a visitor came from: first-touch cookie (landing page, external
 * referrer, UTM tags; set by CaptureFirstTouch) + current page and device.
 */
class VisitorSource
{
    public const COOKIE = 'cs_ft';

    public const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

    /**
     * First-touch data for a request that has no cookie yet.
     *
     * @return array{landing: string, referrer: string|null, utm: array<string, string>, at: string}
     */
    public static function firstTouch(Request $request): array
    {
        $referrer = (string) $request->headers->get('referer', '');
        $external = $referrer !== '' && parse_url($referrer, PHP_URL_HOST) !== $request->getHost();

        return [
            'landing' => mb_substr($request->getRequestUri(), 0, 300),
            'referrer' => $external ? mb_substr($referrer, 0, 300) : null,
            'utm' => static::utm($request->query()),
            'at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, string>
     */
    public static function utm(array $query): array
    {
        $utm = [];

        foreach (self::UTM_KEYS as $key) {
            if (is_string($query[$key] ?? null) && $query[$key] !== '') {
                $utm[substr($key, 4)] = mb_substr($query[$key], 0, 100);
            }
        }

        return $utm;
    }

    /**
     * Source stored on a visitor request.
     *
     * @return array<string, mixed>
     */
    public static function forRequest(Request $request): array
    {
        $first = json_decode((string) $request->cookie(self::COOKIE, ''), true);
        $first = is_array($first) ? $first : [];
        $page = (string) $request->headers->get('referer', '');

        return array_filter([
            'page' => $page !== '' ? mb_substr(rawurldecode((string) parse_url($page, PHP_URL_PATH)), 0, 300) : null,
            'landing' => $first['landing'] ?? null,
            'referrer' => $first['referrer'] ?? null,
            'utm' => is_array($first['utm'] ?? null) && $first['utm'] !== [] ? $first['utm'] : null,
            'device' => static::device((string) $request->userAgent()),
        ]);
    }

    public static function device(string $userAgent): string
    {
        return match (true) {
            (bool) preg_match('/ipad|tablet|(android(?!.*mobile))/i', $userAgent) => 'tablet',
            (bool) preg_match('/mobi|iphone|android/i', $userAgent) => 'mobile',
            default => 'desktop',
        };
    }
}
