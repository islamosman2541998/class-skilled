<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {asset_id, title} — uploaded video or YouTube/Vimeo embed asset. */
class VideoBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            $prefix.'.asset_id' => ['required', 'integer', 'exists:assets,id'],
            ...static::pairRules($prefix.'.title'),
        ];
    }

    public static function defaults(): array
    {
        return ['asset_id' => null, 'title' => ['ar' => '', 'en' => '']];
    }

    public static function sanitize(array $data): array
    {
        return [
            'asset_id' => static::intOrNull($data['asset_id'] ?? null),
            'title' => Translate::pair($data['title'] ?? []),
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
        ];
    }
}
