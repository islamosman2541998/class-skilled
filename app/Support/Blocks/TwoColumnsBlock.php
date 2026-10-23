<?php

namespace App\Support\Blocks;

use App\Support\Translate;
use Illuminate\Support\Collection;

/** {left{title, html, dark}, right{title, html, dark}} — e.g. vision / mission. */
class TwoColumnsBlock extends Block
{
    public static function rules(string $prefix): array
    {
        $rules = [];

        foreach (['left', 'right'] as $side) {
            $rules += static::pairRules($prefix.'.'.$side.'.title', true, 200);
            $rules += static::htmlRules($prefix.'.'.$side.'.html', true);
            $rules[$prefix.'.'.$side.'.dark'] = ['boolean'];
        }

        return $rules;
    }

    public static function defaults(): array
    {
        $column = ['title' => ['ar' => '', 'en' => ''], 'html' => ['ar' => '', 'en' => ''], 'dark' => false];

        return ['left' => $column, 'right' => array_merge($column, ['dark' => true])];
    }

    public static function sanitize(array $data): array
    {
        $clean = [];

        foreach (['left', 'right'] as $side) {
            $column = (array) ($data[$side] ?? []);
            $clean[$side] = [
                'title' => Translate::pair($column['title'] ?? []),
                'html' => static::purifyPair($column['html'] ?? []),
                'dark' => (bool) ($column['dark'] ?? false),
            ];
        }

        return $clean;
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        $view = [];

        foreach (['left', 'right'] as $side) {
            $column = (array) ($data[$side] ?? []);
            $view[$side] = [
                'title' => static::text($column['title'] ?? [], $locale),
                'html' => static::text($column['html'] ?? [], $locale),
                'dark' => (bool) ($column['dark'] ?? false),
            ];
        }

        return $view;
    }
}
