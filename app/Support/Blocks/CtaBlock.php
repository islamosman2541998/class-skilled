<?php

namespace App\Support\Blocks;

use App\Support\LinkResolver;
use App\Support\Translate;
use Illuminate\Support\Collection;

/** {title, text, primary{label, link}, secondary{label, link}} */
class CtaBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            ...static::pairRules($prefix.'.title', true, 200),
            ...static::pairRules($prefix.'.text', false, 1000),
            ...static::pairRules($prefix.'.primary.label', false, 80),
            $prefix.'.primary.link' => ['nullable', 'array'],
            ...static::pairRules($prefix.'.secondary.label', false, 80),
            $prefix.'.secondary.link' => ['nullable', 'array'],
        ];
    }

    public static function defaults(): array
    {
        $button = ['label' => ['ar' => '', 'en' => ''], 'link' => null];

        return ['title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => ''], 'primary' => $button, 'secondary' => $button];
    }

    public static function sanitize(array $data): array
    {
        $button = fn (array $value) => [
            'label' => Translate::pair($value['label'] ?? []),
            'link' => LinkResolver::normalize($value['link'] ?? null),
        ];

        return [
            'title' => Translate::pair($data['title'] ?? []),
            'text' => Translate::pair($data['text'] ?? []),
            'primary' => $button((array) ($data['primary'] ?? [])),
            'secondary' => $button((array) ($data['secondary'] ?? [])),
        ];
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        return [
            'title' => static::text($data['title'] ?? [], $locale),
            'text' => static::text($data['text'] ?? [], $locale),
            'primary' => LinkResolver::button($data['primary'] ?? null, $locale),
            'secondary' => LinkResolver::button($data['secondary'] ?? null, $locale),
        ];
    }
}
