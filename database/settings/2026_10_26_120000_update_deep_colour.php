<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

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
    }
};
