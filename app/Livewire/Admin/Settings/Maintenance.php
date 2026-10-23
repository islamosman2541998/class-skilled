<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\MaintenanceSettings;

class Maintenance extends SettingsPage
{
    public function group(): string
    {
        return 'maintenance';
    }

    protected function settingsClass(): string
    {
        return MaintenanceSettings::class;
    }

    protected function load(array $values): array
    {
        $values['allowed_ips'] = $this->withRepeaterKeys($values['allowed_ips'] ?? []);

        return $values;
    }

    protected function rules(): array
    {
        return [
            'data.enabled' => ['boolean'],
            ...$this->pair('message', false, 1000),
            'data.allowed_ips.*.value' => ['nullable', 'ip'],
        ];
    }

    protected function prepare(array $data): array
    {
        $data['enabled'] = (bool) ($data['enabled'] ?? false);
        $data['allowed_ips'] = $this->stringList($data['allowed_ips'] ?? []);

        return $data;
    }

    public function addCurrentIp(): void
    {
        $ip = (string) request()->ip();

        foreach ($this->data['allowed_ips'] as $item) {
            if (($item['value'] ?? null) === $ip) {
                return;
            }
        }

        $this->addRepeaterItem('data.allowed_ips', ['value' => $ip]);
    }
}
