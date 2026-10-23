<?php

namespace App\Support;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Builds public URLs for a given locale regardless of the current request
 * locale (translated segments from lang/{locale}/routes.php).
 */
class LocalizedRoute
{
    /**
     * @param  array<string, string|int>  $parameters
     */
    public static function url(string $routeName, ?string $locale = null, array $parameters = []): string
    {
        $locale ??= app()->getLocale();

        if ($routeName === 'home') {
            $hidden = $locale === LaravelLocalization::getDefaultLocale() && LaravelLocalization::hideDefaultLocaleInURL();

            return url($hidden ? '/' : '/'.$locale);
        }

        $url = LaravelLocalization::getURLFromRouteNameTranslated($locale, 'routes.'.$routeName, $parameters);

        return is_string($url) ? $url : url('/');
    }

    /**
     * Decoded path (leading slash, no trailing slash) for redirects and comparisons.
     *
     * @param  array<string, string|int>  $parameters
     */
    public static function path(string $routeName, ?string $locale = null, array $parameters = []): string
    {
        return static::normalizePath(static::url($routeName, $locale, $parameters));
    }

    public static function normalizePath(string $urlOrPath): string
    {
        $path = parse_url($urlOrPath, PHP_URL_PATH);
        $path = rawurldecode(is_string($path) ? $path : '/');
        $path = '/'.trim($path, '/');

        return $path;
    }
}
