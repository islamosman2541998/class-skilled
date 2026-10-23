<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {title, items[{title, text, asset_id?}]} */
class CardsBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            ...static::pairRules($prefix.'.title'),
            $prefix.'.items' => ['required', 'array', 'min:1'],
            ...static::pairRules($prefix.'.items.*.title', true, 200),
            ...static::pairRules($prefix.'.items.*.text', false, 1000),
            $prefix.'.items.*.asset_id' => ['nullable', 'integer', 'exists:assets,id'],
        ];
    }

    public static function defaults(): array
    {
        return ['title' => ['ar' => '', 'en' => ''], 'items' => []];
    }

    public static function sanitize(array $data): array
    {
        return [
            'title' => Translate::pair($data['title'] ?? []),
            'items' => array_values(array_map(fn ($item) => [
                'title' => Translate::pair($item['title'] ?? []),
                'text' => Translate::pair($item['text'] ?? []),
                'asset_id' => static::intOrNull($item['asset_id'] ?? null),
            ], (array) ($data['items'] ?? []))),
        ];
    }

    public static function assetIds(array $data): array
    {
        return static::ids(array_map(fn ($item) => $item['asset_id'] ?? null, (array) ($data['items'] ?? [])));
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return [
            'title' => static::text($data['title'] ?? [], $locale),
            'items' => array_map(fn ($item) => [
                'title' => static::text($item['title'] ?? [], $locale),
                'text' => static::text($item['text'] ?? [], $locale),
                'asset' => static::asset($item['asset_id'] ?? null, $assets),
            ], (array) ($data['items'] ?? [])),
        ];
    }
}
