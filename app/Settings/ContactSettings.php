<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public array $phones;

    public ?string $whatsapp_number;

    public array $whatsapp_message;

    public array $emails;

    public array $address;

    public ?float $map_lat;

    public ?float $map_lng;

    public ?string $map_embed_url;

    public array $working_hours;

    public static function group(): string
    {
        return 'contact';
    }
}
