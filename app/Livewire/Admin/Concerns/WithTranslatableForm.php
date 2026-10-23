<?php

namespace App\Livewire\Admin\Concerns;

use App\Support\LocalizedRoute;
use App\Support\Translate;

/**
 * Helpers for forms with `{ar, en}` fields: completeness of the English side
 * (dot in <x-admin.locale-tabs>), validation rules for pairs and slug prefixes.
 */
trait WithTranslatableForm
{
    /**
     * True when any of the given `{ar, en}` values has Arabic but no English.
     *
     * @param  array<int, mixed>  $values
     */
    public function missingEnglish(array $values): bool
    {
        return ! Translate::complete($values);
    }

    /**
     * Rules for a pair: Arabic required (or nullable), English optional.
     *
     * @return array<string, mixed>
     */
    protected function pairRules(string $key, bool $required = true, int $max = 255): array
    {
        return [
            $key => ['array'],
            $key.'.ar' => [$required ? 'required' : 'nullable', 'string', 'max:'.$max],
            $key.'.en' => ['nullable', 'string', 'max:'.$max],
        ];
    }

    /**
     * URL shown before the slug input, e.g. "http://site/en/services/".
     */
    public function slugPrefix(string $routeName, string $locale): string
    {
        $url = LocalizedRoute::url($routeName, $locale, ['slug' => '__slug__']);

        return rawurldecode(str_replace('__slug__', '', $url));
    }

    /**
     * Normalise a typed slug: trim, spaces → dashes, collapse dashes (Arabic kept).
     */
    protected function cleanSlug(?string $slug): string
    {
        $slug = mb_strtolower(trim((string) $slug));
        $slug = preg_replace('/[\s_]+/u', '-', $slug) ?? '';
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug) ?? '';

        return trim(preg_replace('/-+/', '-', $slug) ?? '', '-');
    }
}
