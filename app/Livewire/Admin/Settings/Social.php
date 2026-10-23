<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\SocialSettings;

class Social extends SettingsPage
{
    public const NETWORKS = ['instagram', 'facebook', 'linkedin', 'x', 'tiktok', 'youtube', 'snapchat'];

    public function group(): string
    {
        return 'social';
    }

    protected function settingsClass(): string
    {
        return SocialSettings::class;
    }

    protected function rules(): array
    {
        $rules = [];

        foreach (self::NETWORKS as $network) {
            $rules['data.'.$network] = ['nullable', 'url', 'max:300'];
        }

        return $rules;
    }

    protected function prepare(array $data): array
    {
        foreach (self::NETWORKS as $network) {
            $data[$network] = filled($data[$network] ?? null) ? trim((string) $data[$network]) : null;
        }

        return $data;
    }
}
