<?php

namespace App\Mail;

use App\Enums\RequestType;
use App\Settings\NotificationSettings;
use App\Support\RequestMail;
use App\Support\RequestPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Team notification for a new visitor request (Arabic, the dashboard default): every field grouped
 * in sections, call / WhatsApp / reply / dashboard actions and the visitor's files attached when small enough.
 */
class NewRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Model $request)
    {
        $this->locale('ar');
    }

    public function envelope(): Envelope
    {
        $subject = match (RequestPresenter::type($this->request)) {
            RequestType::Quote => __('mail.new_quote_subject', ['number' => RequestPresenter::number($this->request)], 'ar'),
            RequestType::Message => __('mail.new_message_subject', ['name' => $this->request->getAttribute('name')], 'ar'),
            RequestType::Application => __('mail.new_application_subject', ['name' => $this->request->getAttribute('name')], 'ar'),
        };

        $email = $this->request->getAttribute('email');

        return new Envelope(
            from: new Address((string) config('mail.from.address'), app(NotificationSettings::class)->from_name ?: (string) config('mail.from.name')),
            replyTo: filled($email) ? [new Address((string) $email, (string) $this->request->getAttribute('name'))] : [],
            subject: (string) $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.requests.notification',
            text: 'mail.requests.notification-text',
            with: RequestMail::notification($this->request, 'ar'),
        );
    }

    /**
     * The visitor's files (quote attachments, CV) when their total size fits an e-mail.
     *
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return RequestMail::attachable($this->request)
            ->map(fn (Media $media) => Attachment::fromStorageDisk($media->disk, $media->getPathRelativeToRoot())
                ->as($media->file_name)
                ->withMime((string) $media->mime_type))
            ->values()
            ->all();
    }
}
