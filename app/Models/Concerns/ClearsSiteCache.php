<?php

namespace App\Models\Concerns;

use App\Services\SiteCache;
use App\Services\Sitemap;

/**
 * Any write to a model that the public site reads flushes the site caches.
 * Page-type models (those exposing `publicRouteName()`) also refresh the
 * sitemap once at the end of the request.
 */
trait ClearsSiteCache
{
    public static function bootClearsSiteCache(): void
    {
        $handler = function (self $model): void {
            app(SiteCache::class)->flush();

            if (method_exists($model, 'publicRouteName')) {
                app(Sitemap::class)->regenerateLater();
            }
        };

        static::saved($handler);
        static::deleted($handler);
    }
}
