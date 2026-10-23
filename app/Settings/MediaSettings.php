<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MediaSettings extends Settings
{
    public int $max_image_mb;

    public int $max_video_mb;

    public int $max_image_dimension;

    public int $webp_quality;

    public static function group(): string
    {
        return 'media';
    }
}
