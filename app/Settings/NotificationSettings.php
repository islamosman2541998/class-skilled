<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public array $quote_emails;

    public array $contact_emails;

    public array $careers_emails;

    public ?string $from_name;

    public bool $autoreply_enabled;

    public array $autoreply_subject;

    public array $autoreply_body;

    public static function group(): string
    {
        return 'notifications';
    }
}
