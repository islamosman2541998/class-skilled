<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public array $site_name;

    public array $tagline;

    public array $footer_about;

    public ?int $logo_asset_id;

    public ?int $logo_white_asset_id;

    public ?int $favicon_asset_id;

    public string $default_locale;

    public static function group(): string
    {
        return 'general';
    }
}
