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

    /** Site name shown by Google above the result (WebSite name, og:site_name). */
    public array $search_site_name;

    public array $search_alternate_name;

    /** Home page <title> and meta description. */
    public array $home_title;

    public array $home_description;

    /** Icon next to the result (favicon); falls back to Settings → General favicon. */
    public ?int $search_icon_asset_id;

    /** Organisation logo in structured data; falls back to the site logo. */
    public ?int $search_logo_asset_id;

    public static function group(): string
    {
        return 'seo';
    }
}
