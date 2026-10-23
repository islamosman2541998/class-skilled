<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FormSettings extends Settings
{
    public array $emirates;

    public array $facility_types;

    public array $project_states;

    public array $start_options;

    public array $contact_subjects;

    public int $max_upload_mb;

    public array $allowed_extensions;

    public ?string $turnstile_site_key;

    public ?string $turnstile_secret;

    public bool $honeypot_enabled;

    public static function group(): string
    {
        return 'forms';
    }
}
