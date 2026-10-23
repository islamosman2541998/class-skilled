<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CompanySettings extends Settings
{
    public array $legal_name;

    public string $trade_licence_no;

    public ?string $licence_expiry;

    public array $licence_authority;

    public array $classification;

    public array $classification_authority;

    public ?string $classification_licence_no;

    public ?string $classification_expiry;

    public ?string $adcci_no;

    public ?int $founded_year;

    public static function group(): string
    {
        return 'company';
    }
}
