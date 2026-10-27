<?php

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Services\SiteContext;
use App\Settings\FormSettings;
use App\Settings\NotificationSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * View data for the request e-mails (team notification + visitor auto-reply), shared by the
 * HTML and plain-text templates. Field labels and values come from RequestPresenter.
 */
class RequestMail
{
    /** Visitor files are attached to the team e-mail up to this total size; bigger sets are only listed. */
    public const MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;

    /**
     * Translation keys of each e-mail section, per request type: [contact, details, long text].
     *
     * @var array<string, array{0: list<string>, 1: list<string>, 2: string}>
     */
    private const GROUPS = [
        'quote' => [
            ['forms.name', 'forms.phone', 'forms.email', 'quote.preferred_contact'],
            ['quote.service', 'quote.facility_type', 'quote.project_state', 'quote.emirate', 'quote.area_text', 'quote.area_m2', 'quote.start_when'],
            'quote.description',
        ],
        'message' => [
            ['forms.name', 'forms.phone', 'forms.email'],
            ['forms.subject'],
            'forms.message',
        ],
        'application' => [
            ['forms.name', 'forms.phone', 'forms.email'],
            ['forms.job', 'forms.specialty'],
            'forms.message',
        ],
    ];

    /** Details repeated to the visitor in the auto-reply (no personal data). */
    private const SUMMARY = [
        'quote' => ['quote.service', 'quote.facility_type', 'quote.emirate', 'quote.area_m2', 'quote.start_when'],
        'message' => ['forms.subject'],
        'application' => ['forms.job'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function notification(Model $request, string $locale = 'ar'): array
    {
        $type = RequestPresenter::type($request);
        [$contactKeys, $detailKeys, $longKey] = self::GROUPS[$type->value];
        $fields = RequestPresenter::fields($request, $locale);
        $files = self::files($request);
        $phone = (string) $request->getAttribute('phone');
        $email = (string) $request->getAttribute('email');
        $source = (array) $request->getAttribute('source');
        $visitorLocale = (string) $request->getAttribute('locale');

        $actions = array_values(array_filter([
            ($tel = LinkResolver::phoneUrl($phone)) && $phone !== '' ? ['label' => __('mail.action_call', [], $locale), 'href' => $tel] : null,
            ($wa = self::whatsappUrl($phone)) ? ['label' => __('mail.action_whatsapp', [], $locale), 'href' => $wa] : null,
            filled($email) ? ['label' => __('mail.action_reply', [], $locale), 'href' => 'mailto:'.$email] : null,
        ]));

        return [
            'locale' => $locale,
            'title' => __('mail.type_'.$type->value, [], $locale),
            'number' => RequestPresenter::number($request),
            'date' => self::date($request, $locale),
            'actions' => $actions,
            'dashboardUrl' => RequestPresenter::adminUrl($request),
            'sections' => array_values(array_filter([
                ['title' => __('mail.section_contact', [], $locale), 'rows' => self::pick($fields, $contactKeys, $locale)],
                ['title' => __('mail.section_details_'.$type->value, [], $locale), 'rows' => self::pick($fields, $detailKeys, $locale)],
            ], fn (array $section) => $section['rows'] !== [])),
            'long' => ($body = $fields[__($longKey, [], $locale)] ?? null) !== null ? ['title' => __($longKey, [], $locale), 'body' => $body] : null,
            'files' => $files->map(fn (Media $media) => ['name' => $media->file_name, 'size' => Number::fileSize($media->size, 1)])->all(),
            'filesAttached' => self::attachable($request)->isNotEmpty(),
            'meta' => array_filter([
                __('mail.meta_page', [], $locale) => $source['page'] ?? null,
                __('mail.meta_language', [], $locale) => in_array($visitorLocale, ['ar', 'en'], true) ? __('mail.language_'.$visitorLocale, [], $locale) : null,
                __('mail.meta_device', [], $locale) => isset($source['device']) ? __('mail.device_'.$source['device'], [], $locale) : null,
            ]),
            'site' => self::site($locale),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function autoreply(Model $request, string $locale): array
    {
        $type = RequestPresenter::type($request);
        $settings = app(NotificationSettings::class);
        $fill = fn (string $template) => strtr($template, [
            '{name}' => (string) $request->getAttribute('name'),
            '{number}' => RequestPresenter::number($request),
        ]);

        return [
            'locale' => $locale,
            'title' => __('mail.autoreply_heading', [], $locale),
            'number' => RequestPresenter::number($request),
            'body' => $fill(Translate::pick($settings->autoreply_body, $locale)),
            'summary' => self::pick(RequestPresenter::fields(self::inLocale($request, $locale), $locale), self::SUMMARY[$type->value], $locale),
            'site' => self::site($locale),
        ];
    }

    /**
     * Visitor files (quote attachments, application CV).
     *
     * @return Collection<int, Media>
     */
    public static function files(Model $request): Collection
    {
        return match (true) {
            $request instanceof QuoteRequest => collect($request->getMedia('attachments')),
            $request instanceof JobApplication => collect($request->getMedia('cv')),
            default => collect(),
        };
    }

    /**
     * Files to attach: all of them when their total size fits, otherwise none (they stay in the dashboard).
     *
     * @return Collection<int, Media>
     */
    public static function attachable(Model $request): Collection
    {
        $files = self::files($request);

        return $files->sum('size') <= self::MAX_ATTACHMENT_BYTES ? $files : collect();
    }

    /**
     * WhatsApp link for a number typed by the visitor: local UAE numbers (05x…) get the 971 country code.
     */
    public static function whatsappUrl(string $phone): ?string
    {
        $digits = (string) preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && ! str_starts_with(trim($phone), '+')) {
            $digits = '971'.substr($digits, 1);
        }

        return strlen($digits) >= 8 ? 'https://wa.me/'.$digits : null;
    }

    /**
     * Form choices are stored with their Arabic label (the dashboard language); for the visitor's
     * own e-mail they are shown in the visitor's language, from the same lists in Settings → Forms.
     */
    private static function inLocale(Model $request, string $locale): Model
    {
        $lists = match (true) {
            $request instanceof QuoteRequest => ['facility_type' => 'facility_types', 'project_state' => 'project_states', 'emirate' => 'emirates', 'start_when' => 'start_options'],
            $request instanceof ContactMessage => ['subject' => 'contact_subjects'],
            default => [],
        };

        if ($locale === 'ar' || $lists === []) {
            return $request;
        }

        $settings = app(FormSettings::class);
        $copy = clone $request;

        foreach ($lists as $attribute => $list) {
            $value = $request->getAttribute($attribute);
            $option = collect((array) $settings->{$list})->first(fn ($item) => is_array($item) && ($item['ar'] ?? null) === $value);

            if ($value !== null && $option !== null) {
                $copy->setAttribute($attribute, Translate::pick($option, $locale));
            }
        }

        return $copy;
    }

    /**
     * @param  array<string, string>  $fields
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private static function pick(array $fields, array $keys, string $locale): array
    {
        $rows = [];

        foreach ($keys as $key) {
            $label = (string) __($key, [], $locale);

            if (isset($fields[$label])) {
                $rows[$label] = $fields[$label];
            }
        }

        return $rows;
    }

    private static function date(Model $request, string $locale): string
    {
        $date = $request->getAttribute('created_at') ?? now();

        return $date->copy()->timezone((string) config('app.timezone'))->locale($locale)->translatedFormat('j F Y · H:i');
    }

    /**
     * Company details for the header, contact buttons and footer.
     *
     * @return array<string, mixed>
     */
    private static function site(string $locale): array
    {
        $site = app(SiteContext::class);
        $phone = $site->phones()[0] ?? null;

        return [
            'name' => $site->name($locale),
            'url' => $site->homeUrl($locale),
            'logo' => asset('images/brand/mark-white-sm.png'),
            'address' => $site->address($locale),
            'phones' => array_column($site->phones(), 'label'),
            'emails' => $site->emails(),
            'phoneUrl' => $phone['href'] ?? null,
            'whatsappUrl' => $site->whatsappUrl($locale),
        ];
    }
}
