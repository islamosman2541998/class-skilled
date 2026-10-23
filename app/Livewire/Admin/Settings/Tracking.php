<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\TrackingSettings;

/**
 * Analytics IDs and raw head/body scripts: administrators only.
 */
class Tracking extends SettingsPage
{
    public function group(): string
    {
        return 'tracking';
    }

    protected function settingsClass(): string
    {
        return TrackingSettings::class;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        parent::mount();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        parent::save();
    }

    protected function rules(): array
    {
        return [
            'data.ga4_id' => ['nullable', 'string', 'max:30', 'regex:/^G-[A-Z0-9]+$/'],
            'data.gtm_id' => ['nullable', 'string', 'max:30', 'regex:/^GTM-[A-Z0-9]+$/'],
            'data.meta_pixel_id' => ['nullable', 'string', 'max:30', 'regex:/^\d+$/'],
            'data.head_scripts' => ['nullable', 'string', 'max:20000'],
            'data.body_scripts' => ['nullable', 'string', 'max:20000'],
        ];
    }

    protected function prepare(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = filled($value) ? trim((string) $value) : null;
        }

        return $data;
    }
}
