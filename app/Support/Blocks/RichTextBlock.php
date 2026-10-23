<?php

namespace App\Support\Blocks;

use Illuminate\Support\Collection;

/** {html{ar,en}} — purified on save. */
class RichTextBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return static::htmlRules($prefix.'.html', true);
    }

    public static function defaults(): array
    {
        return ['html' => ['ar' => '', 'en' => '']];
    }

    public static function sanitize(array $data): array
    {
        return ['html' => static::purifyPair($data['html'] ?? [])];
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return ['html' => static::text($data['html'] ?? [], $locale)];
    }
}
