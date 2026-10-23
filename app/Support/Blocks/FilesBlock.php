<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {title, asset_ids[]} — downloadable documents. */
class FilesBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            ...static::pairRules($prefix.'.title'),
            $prefix.'.asset_ids' => ['required', 'array', 'min:1'],
            $prefix.'.asset_ids.*' => ['integer', 'exists:assets,id'],
        ];
    }

    public static function defaults(): array
    {
        return ['title' => ['ar' => '', 'en' => ''], 'asset_ids' => []];
    }

    public static function sanitize(array $data): array
    {
        return [
            'title' => Translate::pair($data['title'] ?? []),
            'asset_ids' => static::ids($data['asset_ids'] ?? []),
        ];
    }

    public static function assetIds(array $data): array
    {
        return static::ids($data['asset_ids'] ?? []);
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return [
            'title' => static::text($data['title'] ?? [], $locale),
            'assets' => static::assetList(static::assetIds($data), $assets),
        ];
    }
}
