<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\CompanySettings;

class Company extends SettingsPage
{
    public function group(): string
    {
        return 'company';
    }

    protected function settingsClass(): string
    {
        return CompanySettings::class;
    }

    protected function rules(): array
    {
        return [
            ...$this->pair('legal_name', true, 200),
            ...$this->pair('licence_authority', false, 200),
            ...$this->pair('classification', false, 200),
            ...$this->pair('classification_authority', false, 200),
            'data.trade_licence_no' => ['required', 'string', 'max:60'],
            'data.licence_expiry' => ['nullable', 'date'],
            'data.classification_licence_no' => ['nullable', 'string', 'max:60'],
            'data.classification_expiry' => ['nullable', 'date'],
            'data.adcci_no' => ['nullable', 'string', 'max:60'],
            'data.founded_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
        ];
    }

    protected function prepare(array $data): array
    {
        $data['founded_year'] = filled($data['founded_year'] ?? null) ? (int) $data['founded_year'] : null;

        foreach (['licence_expiry', 'classification_expiry', 'classification_licence_no', 'adcci_no'] as $key) {
            $data[$key] = filled($data[$key] ?? null) ? (string) $data[$key] : null;
        }

        return $data;
    }
}
