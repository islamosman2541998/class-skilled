<?php

namespace App\Mail;

use App\Settings\NotificationSettings;
use App\Support\RequestPresenter;
use App\Support\Translate;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmation to the visitor in their locale, from the templates in Settings → Notifications
 * (`{name}` and `{number}` placeholders).
 */
class RequestAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public $theme = 'class-skilled';

    public function __construct(public Model $request)
    {
        $this->locale((string) ($request->getAttribute('locale') ?: 'ar'));
    }

    private function fill(string $template): string
    {
        return strtr($template, [
            '{name}' => (string) $this->request->getAttribute('name'),
            '{number}' => RequestPresenter::number($this->request),
        ]);
    }

    public function envelope(): Envelope
    {
        $settings = app(NotificationSettings::class);

        return new Envelope(
            from: new Address((string) config('mail.from.address'), $settings->from_name ?: (string) config('mail.from.name')),
            subject: $this->fill(Translate::pick($settings->autoreply_subject, $this->locale)),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.requests.autoreply', with: [
            'body' => $this->fill(Translate::pick(app(NotificationSettings::class)->autoreply_body, $this->locale)),
        ]);
    }
}
