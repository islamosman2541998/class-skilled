<?php

namespace App\Models\Concerns;

use App\Models\Redirect;
use App\Support\LocalizedRoute;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Translatable slug (unique per locale, generated from the title in each
 * locale that has one). Arabic letters are kept as-is. Changing a slug creates
 * an automatic 301 from the old localized URL to the new one.
 *
 * Models implement `publicRouteName()` (e.g. "services.show").
 */
trait HasTranslatedSlug
{
    use HasTranslatableSlug;

    public static function bootHasTranslatedSlug(): void
    {
        static::updated(function (self $model): void {
            if (! $model->wasChanged('slug')) {
                return;
            }

            $old = $model->getOriginal('slug');
            $old = is_array($old) ? $old : (array) json_decode((string) $old, true);

            foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale) {
                $before = $old[$locale] ?? null;
                $after = $model->getTranslation('slug', $locale, false);

                if (blank($before) || blank($after) || $before === $after) {
                    continue;
                }

                Redirect::createAuto(
                    LocalizedRoute::path($model->publicRouteName(), $locale, ['slug' => $before]),
                    LocalizedRoute::path($model->publicRouteName(), $locale, ['slug' => $after]),
                );
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::createWithLocales(array_keys(config('laravellocalization.supportedLocales')))
            ->generateSlugsFrom($this->slugSourceField())
            ->saveSlugsTo('slug')
            ->usingLanguage('')
            ->slugsShouldBeNoLongerThan(120)
            ->preventOverwrite();
    }

    protected function slugSourceField(): string
    {
        return in_array('title', $this->getTranslatableAttributes(), true) ? 'title' : 'name';
    }

    public function hasLocale(string $locale): bool
    {
        return filled($this->getTranslation($this->slugSourceField(), $locale, false))
            && filled($this->getTranslation('slug', $locale, false));
    }

    public function localizedUrl(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $slug = $this->getTranslation('slug', $locale, false);

        if (blank($slug)) {
            return null;
        }

        return LocalizedRoute::url($this->publicRouteName(), $locale, ['slug' => $slug]);
    }

    /**
     * Only rows that exist in the locale (slug filled), for public listings.
     *
     * @param  Builder<static>  $query
     */
    public function scopeTranslated($query, ?string $locale = null): void
    {
        $column = $this->qualifyColumn('slug').'->'.($locale ?? app()->getLocale());

        $query->whereNotNull($column)->where($column, '!=', '');
    }

    /**
     * Lookup helper for controllers: `Service::whereSlug($slug)->firstOrFail()`.
     *
     * @param  Builder<static>  $query
     */
    public function scopeWhereSlug($query, string $slug, ?string $locale = null): void
    {
        $query->where('slug->'.($locale ?? app()->getLocale()), $slug);
    }
}
