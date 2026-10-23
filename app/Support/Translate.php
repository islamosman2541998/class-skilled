<?php

namespace App\Support;

/**
 * Helpers for `{ar, en}` values stored inside JSON (blocks, sections, settings).
 */
class Translate
{
    /**
     * The value in the requested locale, falling back to Arabic.
     */
    public static function pick(mixed $value, ?string $locale = null, bool $fallback = true): string
    {
        if (! is_array($value)) {
            return is_scalar($value) ? (string) $value : '';
        }

        $locale ??= app()->getLocale();
        $text = $value[$locale] ?? null;

        if (($text === null || $text === '') && $fallback) {
            $text = $value['ar'] ?? '';
        }

        return is_scalar($text) ? (string) $text : '';
    }

    /**
     * A per-locale list (`{ar: [...], en: [...]}`) in the requested locale.
     *
     * @return list<string>
     */
    public static function list(mixed $value, ?string $locale = null): array
    {
        if (! is_array($value)) {
            return [];
        }

        $locale ??= app()->getLocale();
        $items = $value[$locale] ?? [];

        if (! is_array($items) || $items === []) {
            $items = is_array($value['ar'] ?? null) ? $value['ar'] : [];
        }

        return array_values(array_filter(array_map(fn ($item) => is_scalar($item) ? trim((string) $item) : '', $items), fn (string $value) => $value !== ''));
    }

    /**
     * Normalise `{ar, en}` input: trimmed strings, missing locales as ''.
     *
     * @return array{ar: string, en: string}
     */
    public static function pair(mixed $value): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'ar' => trim((string) ($value['ar'] ?? '')),
            'en' => trim((string) ($value['en'] ?? '')),
        ];
    }

    /**
     * Is the English side of every given `{ar, en}` pair filled where Arabic is?
     *
     * @param  array<int, mixed>  $values
     */
    public static function complete(array $values, string $locale = 'en'): bool
    {
        foreach ($values as $value) {
            if (is_array($value) && filled($value['ar'] ?? null) && blank($value[$locale] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
