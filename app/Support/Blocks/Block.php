<?php

namespace App\Support\Blocks;

use App\Models\Asset;
use App\Support\Translate;
use Illuminate\Support\Collection;

/**
 * One content block type. Stored as `{id, type, data}` inside a model's
 * `blocks` JSON; every text inside `data` is an `{ar, en}` pair.
 */
abstract class Block
{
    /**
     * Validation rules for the block data, relative to `$prefix` (e.g. "blocks.0.data").
     *
     * @return array<string, mixed>
     */
    abstract public static function rules(string $prefix): array;

    /**
     * @return array<string, mixed>
     */
    abstract public static function defaults(): array;

    /**
     * Resolve the stored data into what the Blade view needs for one locale.
     *
     * @param  array<string, mixed>  $data
     * @param  Collection<int, Asset>  $assets  preloaded assets keyed by id
     * @param  array<string, mixed>  $context  other preloaded lookups (e.g. faqs)
     * @return array<string, mixed>
     */
    abstract public static function toView(array $data, string $locale, Collection $assets, array $context = []): array;

    /**
     * Clean user input before it is stored (purify HTML, normalise pairs).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function sanitize(array $data): array
    {
        return array_replace_recursive(static::defaults(), $data);
    }

    /**
     * Asset ids referenced by the block (for eager loading and usage tracking).
     *
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    public static function assetIds(array $data): array
    {
        return [];
    }

    /**
     * Rules for a required-Arabic / optional-English text pair.
     *
     * @return array<string, mixed>
     */
    protected static function pairRules(string $key, bool $required = false, int $max = 500): array
    {
        return [
            $key => ['array'],
            $key.'.ar' => [$required ? 'required' : 'nullable', 'string', 'max:'.$max],
            $key.'.en' => ['nullable', 'string', 'max:'.$max],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function htmlRules(string $key, bool $required = false): array
    {
        return static::pairRules($key, $required, 60000);
    }

    /**
     * @return array{ar: string, en: string}
     */
    protected static function purifyPair(mixed $value): array
    {
        $pair = Translate::pair($value);

        return [
            'ar' => static::purify($pair['ar']),
            'en' => static::purify($pair['en']),
        ];
    }

    protected static function purify(string $html): string
    {
        if (trim(strip_tags($html)) === '') {
            return '';
        }

        return (string) clean($html, 'rich');
    }

    protected static function text(mixed $value, string $locale): string
    {
        return Translate::pick($value, $locale);
    }

    /**
     * @param  Collection<int, Asset>  $assets
     */
    protected static function asset(mixed $id, Collection $assets): ?Asset
    {
        return is_numeric($id) ? $assets->get((int) $id) : null;
    }

    /**
     * @param  list<int>  $ids
     * @param  Collection<int, Asset>  $assets
     * @return Collection<int, Asset>
     */
    protected static function assetList(array $ids, Collection $assets): Collection
    {
        return collect($ids)->map(fn (int $id) => $assets->get($id))->filter()->values();
    }

    protected static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    /**
     * @return list<int>
     */
    protected static function ids(mixed $values): array
    {
        $ids = array_map(fn ($value) => (int) $value, array_filter((array) $values, 'is_numeric'));

        return array_values(array_unique(array_filter($ids, fn (int $id) => $id > 0)));
    }
}
