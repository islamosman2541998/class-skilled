<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Base of the dashboard-editable, translatable content models. It adds no
 * behaviour: it lets the generic dashboard screens (lists, forms) rely on the
 * methods every content model gets from spatie/laravel-translatable and the
 * App\Models\Concerns traits.
 *
 * @method mixed getTranslation(string $key, string $locale, bool $useFallbackLocale = true)
 * @method array<string, mixed> getTranslations(?string $key = null, ?array $allowedLocales = null)
 * @method $this setTranslations(string $key, array $translations)
 * @method $this forgetTranslation(string $key, string $locale)
 * @method list<string> getTranslatableAttributes()
 * @method string|null localizedUrl(?string $locale = null)
 * @method bool hasLocale(string $locale)
 * @method string seoTitle(?string $locale = null)
 * @method string seoDescription(?string $locale = null)
 * @method bool seoNoindex()
 * @method MorphOne<SeoMeta, $this> seo()
 * @method static void saveOrder(array<int, int|string> $ids)
 */
abstract class ContentModel extends Model {}
