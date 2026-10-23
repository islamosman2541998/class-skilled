<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {asset_id, title, html, image_side: start|end} */
class MediaTextBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            $prefix.'.asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            $prefix.'.image_side' => ['required', 'in:start,end'],
            ...static::pairRules($prefix.'.title'),
            ...static::htmlRules($prefix.'.html', true),
        ];
    }

    public static function defaults(): array
    {
        return ['asset_id' => null, 'title' => ['ar' => '', 'en' => ''], 'html' => ['ar' => '', 'en' => ''], 'image_side' => 'end'];
    }

    public static function sanitize(array $data): array
    {
        return [
            'asset_id' => static::intOrNull($data['asset_id'] ?? null),
            'title' => Translate::pair($data['title'] ?? []),
            'html' => static::purifyPair($data['html'] ?? []),
            'image_side' => ($data['image_side'] ?? 'end') === 'start' ? 'start' : 'end',
        ];
    }

    public static function assetIds(array $data): array
    {
        return static::ids([$data['asset_id'] ?? null]);
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return [
            'asset' => static::asset($data['asset_id'] ?? null, $assets),
            'title' => static::text($data['title'] ?? [], $locale),
            'html' => static::text($data['html'] ?? [], $locale),
            'image_side' => $data['image_side'] ?? 'end',
        ];
    }
}
