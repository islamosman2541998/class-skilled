<?php

namespace App\Services;

use App\Models\Asset;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\SocialSettings;
use App\Support\LinkResolver;
use App\Support\LocalizedRoute;
use App\Support\Translate;

/**
 * Everything the public layout needs from settings, resolved once per request:
 * names, logos, contact actions, company registration line, social links.
 */
class SiteContext
{
    /** @var array<string, mixed> */
    private array $memo = [];

    public function name(?string $locale = null): string
    {
        return Translate::pick(app(GeneralSettings::class)->site_name, $locale);
    }

    public function tagline(?string $locale = null): string
    {
        return Translate::pick(app(GeneralSettings::class)->tagline, $locale);
    }

    public function footerAbout(?string $locale = null): string
    {
        return Translate::pick(app(GeneralSettings::class)->footer_about, $locale);
    }

    public function logo(bool $white = false): ?Asset
    {
        $key = $white ? 'logo_white' : 'logo';

        if (! array_key_exists($key, $this->memo)) {
            $settings = app(GeneralSettings::class);
            $id = $white ? $settings->logo_white_asset_id : $settings->logo_asset_id;
            $this->memo[$key] = $id ? Asset::query()->with('media')->find($id) : null;
        }

        return $this->memo[$key];
    }

    public function logoUrl(bool $white = false): string
    {
        return $this->logo($white)?->url('sm') ?? asset('images/brand/'.($white ? 'logo-white' : 'logo').'.png');
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    public function phones(): array
    {
        return array_values(array_filter(array_map(fn (string $phone) => ($href = LinkResolver::phoneUrl($phone)) ? ['label' => $phone, 'href' => $href] : null, app(ContactSettings::class)->phones)));
    }

    public function primaryPhoneUrl(): ?string
    {
        return LinkResolver::phoneUrl();
    }

    /**
     * WhatsApp number as entered in Settings → Contact ('' when not set).
     */
    public function whatsappNumber(): string
    {
        return trim((string) app(ContactSettings::class)->whatsapp_number);
    }

    /**
     * The mobile number shown next to WhatsApp: the first phone that is not the WhatsApp number (else the first phone).
     *
     * @return array{label: string, href: string}|null
     */
    public function mobilePhone(): ?array
    {
        $digits = fn (string $n) => preg_replace('/\D+/', '', $n);
        $phones = $this->phones();
        $whatsapp = $digits($this->whatsappNumber());

        foreach ($phones as $phone) {
            if ($digits($phone['label']) !== $whatsapp) {
                return $phone;
            }
        }

        return $phones[0] ?? null;
    }

    public function whatsappUrl(?string $locale = null): ?string
    {
        return LinkResolver::whatsappUrl($locale);
    }

    /**
     * @return list<string>
     */
    public function emails(): array
    {
        return array_values(app(ContactSettings::class)->emails);
    }

    public function address(?string $locale = null): string
    {
        return Translate::pick(app(ContactSettings::class)->address, $locale);
    }

    /**
     * @return list<array{days: string, hours: string}>
     */
    public function workingHours(?string $locale = null): array
    {
        return array_map(fn (array $row) => [
            'days' => Translate::pick($row['days'] ?? [], $locale),
            'hours' => Translate::pick($row['hours'] ?? [], $locale),
        ], app(ContactSettings::class)->working_hours);
    }

    /**
     * @return array<string, string> network => url
     */
    public function social(): array
    {
        return array_filter(app(SocialSettings::class)->toArray(), fn ($url) => filled($url));
    }

    /**
     * Registration line facts for the footer (label => value).
     *
     * @return array<string, string>
     */
    public function registration(?string $locale = null): array
    {
        $company = app(CompanySettings::class);

        return array_filter([
            __('site.trade_licence') => $company->trade_licence_no,
            __('site.classification') => Translate::pick($company->classification, $locale),
            __('site.classification_authority') => Translate::pick($company->classification_authority, $locale),
            __('site.adcci_membership') => (string) $company->adcci_no,
        ]);
    }

    public function legalName(?string $locale = null): string
    {
        return Translate::pick(app(CompanySettings::class)->legal_name, $locale);
    }

    public function quoteUrl(?string $locale = null): string
    {
        return LocalizedRoute::url('quote', $locale);
    }

    public function homeUrl(?string $locale = null): string
    {
        return LocalizedRoute::url('home', $locale);
    }

    /**
     * The CTA band shown before the footer: the home `cta` section when it is
     * visible, otherwise the default UI strings with quote + WhatsApp buttons.
     *
     * @return array{title: string, subtitle: string, primary: array{label: string, url: string, target_blank: bool}|null, secondary: array{label: string, url: string, target_blank: bool}|null}
     */
    public function ctaBand(?string $locale = null): array
    {
        $section = collect(app(HomeSectionResolver::class)->forLocale($locale))->firstWhere('type', 'cta');

        if (is_array($section)) {
            return [
                'title' => (string) $section['title'],
                'subtitle' => (string) $section['subtitle'],
                'primary' => $section['primary'] ?? null,
                'secondary' => $section['secondary'] ?? null,
            ];
        }

        $whatsapp = $this->whatsappUrl($locale);

        return [
            'title' => __('site.cta_title'),
            'subtitle' => __('site.cta_text'),
            'primary' => ['label' => __('site.request_quote'), 'url' => $this->quoteUrl($locale), 'target_blank' => false],
            'secondary' => $whatsapp ? ['label' => __('site.whatsapp_us'), 'url' => $whatsapp, 'target_blank' => true] : null,
        ];
    }
}
