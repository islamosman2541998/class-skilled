<?php

namespace App\Support;

use App\Enums\RequestType;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Settings\NotificationSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared description of visitor requests (quote / message / application):
 * type, number, dashboard route and the labelled fields used by e-mails,
 * the dashboard detail screen and exports.
 */
class RequestPresenter
{
    public static function type(Model $request): RequestType
    {
        return match (true) {
            $request instanceof QuoteRequest => RequestType::Quote,
            $request instanceof JobApplication => RequestType::Application,
            default => RequestType::Message,
        };
    }

    public static function number(Model $request): string
    {
        return match (true) {
            $request instanceof QuoteRequest => (string) $request->number,
            $request instanceof ContactMessage, $request instanceof JobApplication => $request->displayNumber(),
            default => '#'.$request->getKey(),
        };
    }

    public static function route(Model $request): string
    {
        return match (static::type($request)) {
            RequestType::Quote => 'admin.quotes.show',
            RequestType::Message => 'admin.messages.show',
            RequestType::Application => 'admin.applications.show',
        };
    }

    public static function adminUrl(Model $request): string
    {
        return route(static::route($request), $request->getKey());
    }

    /**
     * Labelled values (empty ones dropped), labels in the given locale.
     *
     * @return array<string, string>
     */
    public static function fields(Model $request, ?string $locale = null): array
    {
        return array_map('strval', array_filter(static::allFields($request, $locale), fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * Every field label of the request type, in order (for export headers).
     *
     * @return list<string>
     */
    public static function labels(Model $request, ?string $locale = null): array
    {
        return array_keys(static::allFields($request, $locale));
    }

    /**
     * @return array<string, mixed>
     */
    protected static function allFields(Model $request, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $t = fn (string $key) => (string) __($key, [], $locale);

        $fields = match (true) {
            $request instanceof QuoteRequest => [
                $t('forms.name') => $request->name,
                $t('forms.phone') => $request->phone,
                $t('forms.email') => $request->email,
                $t('quote.service') => $request->service ? (string) $request->service->getTranslation('title', $locale) : null,
                $t('quote.facility_type') => $request->facility_type,
                $t('quote.project_state') => $request->project_state,
                $t('quote.emirate') => $request->emirate,
                $t('quote.area_text') => $request->area_text,
                $t('quote.area_m2') => $request->area_m2 !== null ? rtrim(rtrim((string) $request->area_m2, '0'), '.') : ($request->exists ? $t('quote.area_unknown') : null),
                $t('quote.start_when') => $request->start_when,
                $t('quote.preferred_contact') => $request->preferred_contact ? $t('quote.prefer_'.$request->preferred_contact) : null,
                $t('quote.description') => $request->description,
            ],
            $request instanceof JobApplication => [
                $t('forms.name') => $request->name,
                $t('forms.phone') => $request->phone,
                $t('forms.email') => $request->email,
                $t('forms.job') => $request->jobPost ? (string) $request->jobPost->getTranslation('title', $locale) : null,
                $t('forms.specialty') => $request->specialty,
                $t('forms.message') => $request->message,
            ],
            $request instanceof ContactMessage => [
                $t('forms.name') => $request->name,
                $t('forms.phone') => $request->phone,
                $t('forms.email') => $request->email,
                $t('forms.subject') => $request->subject,
                $t('forms.message') => $request->message,
            ],
            default => [],
        };

        return $fields;
    }

    /**
     * Notification recipients from NotificationSettings.
     *
     * @return list<string>
     */
    public static function recipients(RequestType $type): array
    {
        $settings = app(NotificationSettings::class);

        $emails = match ($type) {
            RequestType::Quote => $settings->quote_emails,
            RequestType::Message => $settings->contact_emails,
            RequestType::Application => $settings->careers_emails,
        };

        return array_values(array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)));
    }
}
