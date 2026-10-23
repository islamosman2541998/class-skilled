<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\ContentModel;
use App\Settings\SeoSettings;
use App\Support\Translate;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Per-request page meta for the public layout: title, description, canonical,
 * the URL of the same page in every locale (language switch + hreflang),
 * Open Graph image/type, breadcrumbs and page-specific JSON-LD nodes.
 * Controllers and views fill it; the layout head reads it.
 */
class SeoManager
{
    private ?string $title = null;

    private ?string $description = null;

    /** @var array<string, string|null> */
    private array $alternates = [];

    private bool $noindex = false;

    private ?ContentModel $model = null;

    private ?Asset $image = null;

    private string $type = 'website';

    /** @var array<string, string|null> */
    private array $breadcrumbs = [];

    /** @var list<array<string, mixed>> */
    private array $schema = [];

    /**
     * Meta of a page-type model (HasSeo overrides, translated URLs).
     */
    public function forModel(ContentModel $model, bool $preview = false): static
    {
        $this->model = $model;
        $this->title = $model->seoTitle() ?: null;
        $this->description = $model->seoDescription() ?: null;
        $this->noindex = $preview || $model->seoNoindex();

        if (method_exists($model, 'localizedUrl')) {
            $this->alternates = collect(LaravelLocalization::getSupportedLanguagesKeys())
                ->mapWithKeys(fn (string $locale) => [$locale => $model->hasLocale($locale) ? $model->localizedUrl($locale) : null])
                ->all();
        }

        return $this;
    }

    public function model(): ?ContentModel
    {
        return $this->model;
    }

    public function noindex(?bool $noindex = null): static|bool
    {
        if ($noindex !== null) {
            $this->noindex = $noindex;

            return $this;
        }

        return $this->noindex;
    }

    /**
     * Locales the current page exists in (hreflang).
     *
     * @return list<string>
     */
    public function availableLocales(): array
    {
        return array_values(array_filter(
            LaravelLocalization::getSupportedLanguagesKeys(),
            fn (string $locale) => ! array_key_exists($locale, $this->alternates) || $this->alternates[$locale] !== null,
        ));
    }

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

    /**
     * Canonical URL: the SEO override, else this page in the current locale without query string.
     */
    public function canonical(): string
    {
        $override = $this->model?->getRelationValue('seo')?->getTranslation('canonical', app()->getLocale(), false);

        if (filled($override) && preg_match('~^https?://~i', (string) $override)) {
            return (string) $override;
        }

        $url = $this->alternateUrl(app()->getLocale());

        return strtok($url, '?') ?: $url;
    }

    public function image(?Asset $image = null): static|string|null
    {
        if ($image !== null) {
            $this->image = $image;

            return $this;
        }

        $asset = $this->image ?? $this->model?->seoImage();

        if ($asset === null) {
            $defaultId = app(SeoSettings::class)->default_og_asset_id;
            $asset = $defaultId ? Asset::query()->with('media')->find($defaultId) : null;
        }

        return $asset?->url('lg') ?: app(SiteContext::class)->logoUrl();
    }

    public function type(?string $type = null): static|string
    {
        if ($type !== null) {
            $this->type = $type;

            return $this;
        }

        return $this->type;
    }

    /**
     * Breadcrumb trail of the page (label => url|null), for the BreadcrumbList schema.
     *
     * @param  array<string, string|null>|null  $trail
     * @return static|array<string, string|null>
     */
    public function breadcrumbs(?array $trail = null): static|array
    {
        if ($trail !== null) {
            $this->breadcrumbs = $trail;

            return $this;
        }

        return $this->breadcrumbs;
    }

    /**
     * Add a JSON-LD node for this page (Service, Article, FAQPage, JobPosting …).
     *
     * @param  array<string, mixed>  $node
     */
    public function schema(array $node): static
    {
        $this->schema[] = $node;

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function schemaNodes(): array
    {
        return $this->schema;
    }

    private function documentTitle(): string
    {
        $site = app(SiteContext::class)->name();
        $suffix = Translate::pick(app(SeoSettings::class)->title_suffix);

        $brand = $suffix !== '' ? $suffix : $site;

        return $this->title === null || $this->title === '' ? $brand : $this->title.' | '.$brand;
    }
}
