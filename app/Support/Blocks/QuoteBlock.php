<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {text, author} */
class QuoteBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            ...static::pairRules($prefix.'.text', true, 1000),
            ...static::pairRules($prefix.'.author', false, 200),
        ];
    }

    public static function defaults(): array
    {
        return ['text' => ['ar' => '', 'en' => ''], 'author' => ['ar' => '', 'en' => '']];
    }

    public static function sanitize(array $data): array
    {
        return ['text' => Translate::pair($data['text'] ?? []), 'author' => Translate::pair($data['author'] ?? [])];
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return ['text' => static::text($data['text'] ?? [], $locale), 'author' => static::text($data['author'] ?? [], $locale)];
    }
}
