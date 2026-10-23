<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\SystemSettings;

class System extends SettingsPage
{
    public function group(): string
    {
        return 'system';
    }

    protected function settingsClass(): string
    {
        return SystemSettings::class;
    }

    protected function rules(): array
    {
        return [
            'data.backup_keep' => ['required', 'integer', 'min:1', 'max:30'],
            'data.admin_session_minutes' => ['required', 'integer', 'min:10', 'max:1440'],
        ];
    }

    protected function prepare(array $data): array
    {
        return array_map('intval', $data);
    }
}
