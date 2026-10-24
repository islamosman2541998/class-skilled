<?php

namespace App\Livewire\Admin\Settings;

use App\Services\FaviconGenerator;
use App\Settings\GeneralSettings;
use Spatie\LaravelSettings\Settings;

class General extends SettingsPage
{
    public function group(): string
    {
        return 'general';
    }

    protected function settingsClass(): string
    {
        return GeneralSettings::class;
    }

    protected function rules(): array
    {
        return [
            ...$this->pair('site_name', true, 120),
            ...$this->pair('tagline', false, 160),
            ...$this->pair('footer_about', false, 400),
            'data.logo_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'data.logo_white_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'data.favicon_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'data.default_locale' => ['required', 'in:ar,en'],
        ];
    }

    protected function attributes(): array
    {
        return ['data.site_name.ar' => __('admin.settings.fields.site_name')];
    }

    protected function prepare(array $data): array
    {
        foreach (['logo_asset_id', 'logo_white_asset_id', 'favicon_asset_id'] as $key) {
            $data[$key] = $data[$key] ? (int) $data[$key] : null;
        }

        return $data;
    }

    protected function afterSave(Settings $settings): void
    {
        app(FaviconGenerator::class)->refresh();
    }
}
