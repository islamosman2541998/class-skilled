<?php

namespace App\Services;

use App\Settings\SeoSettings;
use App\Support\Translate;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Per-request page meta for the public layout: title, description and the
 * URL of the same page in every locale (language switch + hreflang).
 * Controllers fill it; the layout reads it. Extended with OG/schema in phase 09.
 */
class SeoManager
{
    private ?string $title = null;

    private ?string $description = null;

    /** @var array<string, string|null> */
    private array $alternates = [];

    public function title(?string $title = null): static|string
    {
        if ($title !== null) {
            $this->title = $title;

            return $this;
        }

        return $this->documentTitle();
    }

    public function description(?string $description = null): static|string
    {
        if ($description !== null) {
            $this->description = $description;

            return $this;
        }

        return $this->description ?: Translate::pick(app(SeoSettings::class)->default_description);
    }

    /**
     * @param  array<string, string|null>  $urls  locale => url (null when the page has no translation)
     */
    public function alternates(array $urls): static
    {
        $this->alternates = $urls;

        return $this;
    }

    /**
     * URL of the current page in another locale. Pages that do not exist in that
     * locale fall back to its home page.
     */
    public function alternateUrl(string $locale): string
    {
        if (array_key_exists($locale, $this->alternates)) {
            return $this->alternates[$locale] ?? app(SiteContext::class)->homeUrl($locale);
        }

        $url = LaravelLocalization::getLocalizedURL($locale, null, [], true);

        return is_string($url) ? $url : app(SiteContext::class)->homeUrl($locale);
    }

    private function documentTitle(): string
    {
        $site = app(SiteContext::class)->name();
        $suffix = Translate::pick(app(SeoSettings::class)->title_suffix);

        $brand = $suffix !== '' ? $suffix : $site;

        return $this->title === null || $this->title === '' ? $brand : $this->title.' | '.$brand;
    }
}
