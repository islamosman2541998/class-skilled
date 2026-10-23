<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {asset_ids[], columns 2|3|4, title} */
class GalleryBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            $prefix.'.asset_ids' => ['required', 'array', 'min:1'],
            $prefix.'.asset_ids.*' => ['integer', 'exists:assets,id'],
            $prefix.'.columns' => ['required', 'in:2,3,4'],
            ...static::pairRules($prefix.'.title'),
        ];
    }

    public static function defaults(): array
    {
        return ['asset_ids' => [], 'columns' => 3, 'title' => ['ar' => '', 'en' => '']];
    }

    public static function sanitize(array $data): array
    {
        $columns = (int) ($data['columns'] ?? 3);

        return [
            'asset_ids' => static::ids($data['asset_ids'] ?? []),
            'columns' => in_array($columns, [2, 3, 4], true) ? $columns : 3,
            'title' => Translate::pair($data['title'] ?? []),
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
            'columns' => (int) ($data['columns'] ?? 3),
            'assets' => static::assetList(static::assetIds($data), $assets),
        ];
    }
}
