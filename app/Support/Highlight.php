<?php

namespace App\Support;

/**
 * Escapes a text and wraps case-insensitive matches of the term in <mark>.
 */
class Highlight
{
    public static function mark(string $text, string $term): string
    {
        $escaped = e($text);
        $term = trim($term);

        if ($term === '') {
            return $escaped;
        }

        return (string) preg_replace('/('.preg_quote(e($term), '/').')/iu', '<mark class="rounded bg-sky/25 px-0.5 text-inherit">$1</mark>', $escaped);
    }
}
