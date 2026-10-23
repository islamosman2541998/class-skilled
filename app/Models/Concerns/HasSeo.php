<?php

namespace App\Models\Concerns;

use App\Models\Asset;
use App\Models\SeoMeta;
use App\Settings\SeoSettings;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * SEO overrides with fallbacks: SEO field → model content → global SEO settings.
 *
 * @property-read SeoMeta|null $seo
 */
trait HasSeo
{
    public static function bootHasSeo(): void
    {
        static::deleting(function (self $model): void {
            $model->seo()->delete();
        });
    }

    /**
     * @return MorphOne<SeoMeta, $this>
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function seoTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $title = $this->seo?->getTranslation('title', $locale, false);

        return filled($title) ? $title : (string) $this->translatedValue($this->seoTitleField(), $locale);
    }

    public function seoDescription(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $description = $this->seo?->getTranslation('description', $locale, false);

        if (blank($description)) {
            foreach ($this->seoDescriptionFields() as $field) {
                $description = $this->translatedValue($field, $locale);

                if (filled($description)) {
                    break;
                }
            }
        }

        if (blank($description)) {
            $description = app(SeoSettings::class)->default_description[$locale] ?? '';
        }

        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $description)) ?? ''), 160);
    }

    public function seoImage(): ?Asset
    {
        if ($this->seo?->ogAsset) {
            return $this->seo->ogAsset;
        }

        if (method_exists($this, 'firstAsset') && ($cover = $this->firstAsset('cover'))) {
            return $cover;
        }

        $defaultId = app(SeoSettings::class)->default_og_asset_id;

        return $defaultId ? Asset::find($defaultId) : null;
    }

    public function seoNoindex(): bool
    {
        return $this->seo?->robots === 'noindex';
    }

    protected function seoTitleField(): string
    {
        return in_array('title', $this->getTranslatableAttributes(), true) ? 'title' : 'name';
    }

    /**
     * @return list<string>
     */
    protected function seoDescriptionFields(): array
    {
        return ['excerpt', 'summary'];
    }

    protected function translatedValue(string $field, string $locale): mixed
    {
        if (! in_array($field, $this->getTranslatableAttributes(), true)) {
            return null;
        }

        return $this->getTranslation($field, $locale, false) ?: $this->getTranslation($field, 'ar', false);
    }
}
