<?php

namespace App\Mail;

use App\Settings\NotificationSettings;
use App\Support\RequestMail;
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
 * Confirmation to the visitor in their locale: the text from Settings → Notifications
 * (`{name}` and `{number}` placeholders), a summary of the request and the company contact buttons.
 */
class RequestAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Model $request)
    {
        $this->locale((string) ($request->getAttribute('locale') ?: 'ar'));
    }

    public function envelope(): Envelope
    {
        $settings = app(NotificationSettings::class);
        $subject = strtr(Translate::pick($settings->autoreply_subject, $this->locale), [
            '{name}' => (string) $this->request->getAttribute('name'),
            '{number}' => RequestPresenter::number($this->request),
        ]);

        return new Envelope(
            from: new Address((string) config('mail.from.address'), $settings->from_name ?: (string) config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.requests.autoreply',
            text: 'mail.requests.autoreply-text',
            with: RequestMail::autoreply($this->request, (string) $this->locale),
        );
    }
}
