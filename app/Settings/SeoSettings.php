<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public array $title_suffix;

    public array $default_description;

    public ?int $default_og_asset_id;

    public ?string $google_verification;

    public ?string $bing_verification;

    public string $robots_txt;

    public array $schema_extra;

    public static function group(): string
    {
        return 'seo';
    }
}
