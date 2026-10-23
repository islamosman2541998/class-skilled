<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\FormSettings;

class Forms extends SettingsPage
{
    public const LISTS = ['emirates', 'facility_types', 'project_states', 'start_options', 'contact_subjects'];

    public const EXTENSIONS = ['pdf', 'dwg', 'jpg', 'jpeg', 'png', 'webp', 'zip'];

    public function group(): string
    {
        return 'forms';
    }

    protected function settingsClass(): string
    {
        return FormSettings::class;
    }

    protected function load(array $values): array
    {
        foreach (self::LISTS as $key) {
            $values[$key] = $this->withRepeaterKeys($values[$key] ?? []);
        }

        return $values;
    }

    protected function rules(): array
    {
        $rules = [
            'data.max_upload_mb' => ['required', 'integer', 'min:1', 'max:100'],
            'data.allowed_extensions' => ['array', 'min:1'],
            'data.allowed_extensions.*' => ['in:'.implode(',', self::EXTENSIONS)],
            'data.turnstile_site_key' => ['nullable', 'string', 'max:200'],
            'data.turnstile_secret' => ['nullable', 'string', 'max:200'],
            'data.honeypot_enabled' => ['boolean'],
        ];

        foreach (self::LISTS as $key) {
            $rules['data.'.$key.'.*.ar'] = ['required', 'string', 'max:120'];
            $rules['data.'.$key.'.*.en'] = ['nullable', 'string', 'max:120'];
        }

        return $rules;
    }

    protected function prepare(array $data): array
    {
        foreach (self::LISTS as $key) {
            $data[$key] = $this->pairList($data[$key] ?? []);
        }

        $data['max_upload_mb'] = (int) $data['max_upload_mb'];
        $data['allowed_extensions'] = array_values(array_intersect(self::EXTENSIONS, (array) ($data['allowed_extensions'] ?? [])));
        $data['honeypot_enabled'] = (bool) ($data['honeypot_enabled'] ?? true);

        foreach (['turnstile_site_key', 'turnstile_secret'] as $key) {
            $data[$key] = filled($data[$key] ?? null) ? trim((string) $data[$key]) : null;
        }

        return $data;
    }
}
