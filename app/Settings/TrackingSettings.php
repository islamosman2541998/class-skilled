<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TrackingSettings extends Settings
{
    public ?string $ga4_id;

    public ?string $gtm_id;

    public ?string $meta_pixel_id;

    public ?string $head_scripts;

    public ?string $body_scripts;

    public static function group(): string
    {
        return 'tracking';
    }
}
