<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppearanceSettings extends Settings
{
    public string $heading_font;

    public string $body_font;

    public float $base_font_size;

    public float $line_height_ar;

    public array $colors;

    public static function group(): string
    {
        return 'appearance';
    }
}
