<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\ContactSettings;
use App\Support\LinkResolver;

class Contact extends SettingsPage
{
    public function group(): string
    {
        return 'contact';
    }

    protected function settingsClass(): string
    {
        return ContactSettings::class;
    }

    protected function load(array $values): array
    {
        $values['phones'] = $this->withRepeaterKeys($values['phones'] ?? []);
        $values['emails'] = $this->withRepeaterKeys($values['emails'] ?? []);
        $values['working_hours'] = $this->withRepeaterKeys($values['working_hours'] ?? []);

        return $values;
    }

    protected function rules(): array
    {
        return [
            'data.phones' => ['array'],
            'data.phones.*.value' => ['nullable', 'string', 'max:40', 'regex:/^[+\d\s\-()]+$/'],
            'data.whatsapp_number' => ['nullable', 'string', 'max:40', 'regex:/^[+\d\s\-()]+$/'],
            ...$this->pair('whatsapp_message', false, 300),
            'data.emails' => ['array'],
            'data.emails.*.value' => ['nullable', 'email', 'max:190'],
            ...$this->pair('address', false, 300),
            'data.map_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'data.map_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'data.map_embed_url' => ['nullable', 'url', 'max:2000', 'starts_with:https://www.google.com/maps,https://maps.google.com'],
            'data.working_hours' => ['array'],
            'data.working_hours.*.days.ar' => ['required', 'string', 'max:100'],
            'data.working_hours.*.days.en' => ['nullable', 'string', 'max:100'],
            'data.working_hours.*.hours.ar' => ['required', 'string', 'max:100'],
            'data.working_hours.*.hours.en' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepare(array $data): array
    {
        $data['phones'] = $this->stringList($data['phones'] ?? []);
        $data['emails'] = $this->stringList($data['emails'] ?? []);
        $data['working_hours'] = array_values(array_map(fn (array $row) => [
            'days' => ['ar' => trim((string) ($row['days']['ar'] ?? '')), 'en' => trim((string) ($row['days']['en'] ?? ''))],
            'hours' => ['ar' => trim((string) ($row['hours']['ar'] ?? '')), 'en' => trim((string) ($row['hours']['en'] ?? ''))],
        ], $data['working_hours'] ?? []));
        $data['map_lat'] = filled($data['map_lat'] ?? null) ? (float) $data['map_lat'] : null;
        $data['map_lng'] = filled($data['map_lng'] ?? null) ? (float) $data['map_lng'] : null;
        $data['whatsapp_number'] = filled($data['whatsapp_number'] ?? null) ? trim((string) $data['whatsapp_number']) : null;
        $data['map_embed_url'] = filled($data['map_embed_url'] ?? null) ? trim((string) $data['map_embed_url']) : null;

        return $data;
    }

    public function whatsappPreview(string $locale): ?string
    {
        $number = preg_replace('/\D+/', '', (string) ($this->data['whatsapp_number'] ?? ''));
        $text = (string) ($this->data['whatsapp_message'][$locale] ?? '');

        return $number ? 'https://wa.me/'.$number.($text !== '' ? '?text='.rawurlencode($text) : '') : null;
    }

    protected function viewData(): array
    {
        return ['linkResolver' => LinkResolver::class];
    }
}
