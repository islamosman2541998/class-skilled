<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public int $backup_keep;

    public int $admin_session_minutes;

    public static function group(): string
    {
        return 'system';
    }
}
