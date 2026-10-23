<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MaintenanceSettings extends Settings
{
    public bool $enabled;

    public array $message;

    public array $allowed_ips;

    public static function group(): string
    {
        return 'maintenance';
    }
}
