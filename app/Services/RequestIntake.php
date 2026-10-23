<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\SiteEventType;
use App\Mail\NewRequestNotification;
use App\Mail\RequestAutoReply;
use App\Models\RequestStatus;
use App\Models\SiteEvent;
use App\Models\VisitorRequest;
use App\Settings\FormSettings;
use App\Settings\NotificationSettings;
use App\Support\RequestPresenter;
use App\Support\Turnstile;
use App\Support\VisitorSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Shared flow of the public forms (quote / contact / application):
 * spam checks + rate limit before saving, then default status, system
 * activity, site event and e-mails sent after the response.
 */
class RequestIntake
{
    public const MIN_SECONDS = 3;

    public const MAX_PER_HOUR = 5;

    /**
     * Returns a UI string key describing why the submission is refused, or null.
     */
    public function reject(string $form, ?string $honeypot, int $startedAt, ?string $turnstileToken = null): ?string
    {
        if (app(FormSettings::class)->honeypot_enabled) {
            if (filled($honeypot)) {
                return 'forms.spam';
            }

            if (now()->getTimestamp() - $startedAt < self::MIN_SECONDS) {
                return 'forms.too_fast';
            }
        }

        if (! Turnstile::verify($turnstileToken, request()->ip())) {
            return 'forms.spam';
        }

        $key = 'form:'.$form.':'.$this->ipHash();

        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            return 'forms.too_many';
        }

        RateLimiter::hit($key, 3600);

        return null;
    }

    /**
     * Attributes every visitor request gets on creation.
     *
     * @return array<string, mixed>
     */
    public function baseAttributes(): array
    {
        return [
            'locale' => app()->getLocale(),
            'source' => VisitorSource::forRequest(request()),
            'ip_hash' => $this->ipHash(),
        ];
    }

    public function ipHash(): string
    {
        return hash_hmac('sha256', (string) request()->ip(), (string) config('app.key'));
    }

    public function finalize(VisitorRequest $request, SiteEventType $event): void
    {
        $type = RequestPresenter::type($request);

        if ($request->getAttribute('status_id') === null) {
            $request->forceFill(['status_id' => RequestStatus::defaultFor($type)?->getKey()])->saveQuietly();
        }

        $request->logActivity(ActivityType::System->value, null, ['event' => 'created', 'source' => $request->getAttribute('source')]);

        $source = (array) $request->getAttribute('source');
        SiteEvent::query()->create([
            'type' => $event,
            'path' => $source['page'] ?? null,
            'locale' => $request->getAttribute('locale'),
            'device' => $source['device'] ?? null,
            'utm' => $source['utm'] ?? null,
        ]);

        AdminNavigation::forgetCounts();

        // Shared hosting has no queue worker: send once the visitor already has the response.
        dispatch(fn () => $this->sendMails($request))->afterResponse();
    }

    public function sendMails(Model $request): void
    {
        $type = RequestPresenter::type($request);

        try {
            $recipients = RequestPresenter::recipients($type);

            if ($recipients !== []) {
                Mail::to($recipients)->send(new NewRequestNotification($request));
            }

            $email = $request->getAttribute('email');

            if (filled($email) && app(NotificationSettings::class)->autoreply_enabled) {
                Mail::to((string) $email)->send(new RequestAutoReply($request));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
