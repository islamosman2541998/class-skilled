<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * Translated labels for backed enums. Labels live in the `enums` group of
 * `language_lines` (key = snake_case enum name + "." + value).
 */
trait HasLabel
{
    public function label(): string
    {
        return __('enums.'.Str::snake(class_basename(static::class)).'.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
