<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public ?string $instagram;

    public ?string $facebook;

    public ?string $linkedin;

    public ?string $x;

    public ?string $tiktok;

    public ?string $youtube;

    public ?string $snapchat;

    public static function group(): string
    {
        return 'social';
    }
}
