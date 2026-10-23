<?php

namespace App\Livewire\Admin\Settings;

use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Notifications extends SettingsPage
{
    public function group(): string
    {
        return 'notifications';
    }

    protected function settingsClass(): string
    {
        return NotificationSettings::class;
    }

    protected function load(array $values): array
    {
        foreach (['quote_emails', 'contact_emails', 'careers_emails'] as $key) {
            $values[$key] = $this->withRepeaterKeys($values[$key] ?? []);
        }

        return $values;
    }

    protected function rules(): array
    {
        return [
            'data.quote_emails.*.value' => ['nullable', 'email', 'max:190'],
            'data.contact_emails.*.value' => ['nullable', 'email', 'max:190'],
            'data.careers_emails.*.value' => ['nullable', 'email', 'max:190'],
            'data.from_name' => ['nullable', 'string', 'max:120'],
            'data.autoreply_enabled' => ['boolean'],
            ...$this->pair('autoreply_subject', false, 200),
            ...$this->pair('autoreply_body', false, 3000),
        ];
    }

    protected function prepare(array $data): array
    {
        foreach (['quote_emails', 'contact_emails', 'careers_emails'] as $key) {
            $data[$key] = $this->stringList($data[$key] ?? []);
        }

        $data['autoreply_enabled'] = (bool) ($data['autoreply_enabled'] ?? false);
        $data['from_name'] = filled($data['from_name'] ?? null) ? trim((string) $data['from_name']) : null;

        return $data;
    }

    public function sendTest(): void
    {
        abort_unless(auth()->user()?->can($this->permission()), 403);
        $email = (string) auth()->user()->email;

        try {
            Mail::raw(__('admin.settings.test_email_body'), function ($message) use ($email): void {
                $message->to($email)->subject(__('admin.settings.test_email_subject'));
            });
            $this->toast('success', __('admin.settings.test_email_sent', ['email' => $email]));
        } catch (Throwable $e) {
            report($e);
            $this->toast('error', __('admin.settings.test_email_failed'));
        }
    }
}
