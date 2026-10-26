<?php

use App\Services\SiteCache;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

/**
 * New default for the deep colour (top bar, footer, dark sections): #0C122B.
 * Only replaces the old default, so a colour picked in the dashboard is kept.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('appearance.colors', function (array|object $colors): array {
            $colors = (array) $colors;

            if (strtoupper((string) ($colors['deep'] ?? '')) === '#1B2A47') {
                $colors['deep'] = '#0C122B';
            }

            return $colors;
        });

        // The settings cache and the theme/page caches still hold the old colour: clear them,
        // so running `php artisan migrate` on the server is enough to see the new one.
        foreach (app(SettingsCacheFactory::class)->all() as $cache) {
            $cache->clear();
        }
        app(SiteCache::class)->flush();
    }
};
