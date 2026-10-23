<?php

namespace App\Services;

use App\Settings\AppearanceSettings;
use Illuminate\Support\Facades\Vite;

/**
 * Site theme from AppearanceSettings: the `<style>` with CSS variables
 * (cached in SiteCache), the font CSS entries of the chosen families only,
 * and WCAG contrast helpers for the dashboard warnings.
 */
class Appearance
{
    public const DEFAULT_COLORS = [
        'navy' => '#2B4168', 'deep' => '#1B2A47', 'sky' => '#4FC3EE', 'ink' => '#24324D',
        'muted' => '#5D6A80', 'line' => '#E8ECF1', 'paper' => '#F5F7FA',
    ];

    public const FONTS = [
        'cairo' => ['family' => "'Cairo', sans-serif", 'css' => 'resources/css/fonts/cairo.css'],
        'tajawal' => ['family' => "'Tajawal', sans-serif", 'css' => 'resources/css/fonts/tajawal.css'],
    ];

    public function __construct(private readonly SiteCache $cache) {}

    public function styleTag(): string
    {
        return $this->cache->remember('appearance.style', fn () => '<style id="cs-theme">'.$this->css(app(AppearanceSettings::class)->toArray()).'</style>');
    }

    /**
     * @param  array<string, mixed>  $values  AppearanceSettings::toArray() (or live form data)
     */
    public function css(array $values): string
    {
        $colors = array_merge(self::DEFAULT_COLORS, array_filter((array) ($values['colors'] ?? []), fn ($hex) => self::isHex((string) $hex)));
        $heading = self::FONTS[$values['heading_font'] ?? 'cairo']['family'] ?? self::FONTS['cairo']['family'];
        $body = self::FONTS[$values['body_font'] ?? 'tajawal']['family'] ?? self::FONTS['tajawal']['family'];
        $size = max(14, min(18, (float) ($values['base_font_size'] ?? 15.5)));
        $lineHeight = max(1.5, min(2.1, (float) ($values['line_height_ar'] ?? 1.85)));

        $vars = [];

        foreach ($colors as $name => $hex) {
            $vars[] = '--c-'.$name.':'.strtoupper((string) $hex);
        }

        $vars[] = '--font-head:'.$heading;
        $vars[] = '--font-body:'.$body;
        $vars[] = '--base-size:'.$size.'px';
        $vars[] = '--lh-ar:'.$lineHeight;

        return ':root{'.implode(';', $vars).'}';
    }

    /**
     * Vite CSS entries for the chosen families (only these download).
     *
     * @return list<string>
     */
    public function fontEntries(): array
    {
        $settings = app(AppearanceSettings::class);

        return array_values(array_unique(array_filter([
            self::FONTS[$settings->heading_font]['css'] ?? null,
            self::FONTS[$settings->body_font]['css'] ?? null,
        ])));
    }

    /**
     * URL of the heading font file used above the fold (800 weight, locale subset), for <link rel="preload">.
     */
    public function preloadFontUrl(?string $locale = null): ?string
    {
        $family = app(AppearanceSettings::class)->heading_font === 'tajawal' ? 'tajawal' : 'cairo';
        $subset = ($locale ?? app()->getLocale()) === 'ar' ? 'arabic' : 'latin';

        try {
            return Vite::asset("node_modules/@fontsource/{$family}/files/{$family}-{$subset}-800-normal.woff2");
        } catch (\Throwable) {
            return null;
        }
    }

    public static function isHex(string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
    }

    /**
     * WCAG contrast ratio between two #RRGGBB colours.
     */
    public static function contrast(string $a, string $b): float
    {
        if (! self::isHex($a) || ! self::isHex($b)) {
            return 21.0;
        }

        $l1 = self::luminance($a);
        $l2 = self::luminance($b);

        return round((max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05), 2);
    }

    private static function luminance(string $hex): float
    {
        $channels = [];

        foreach ([1, 3, 5] as $offset) {
            $c = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
