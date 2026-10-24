<?php

namespace App\Services;

use App\Models\Asset;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Builds the site icon set with GD into the public disk (favicons/):
 * PNGs 32/48/96/180/192/512 and a favicon.ico (PNG inside an ICO container,
 * served at /favicon.ico — the file Google reads for the icon next to a result).
 * Source: Settings → SEO search icon, else Settings → General favicon, else the brand mark.
 */
class FaviconGenerator
{
    public const SIZES = [32, 48, 96, 180, 192, 512];

    public function __construct(private readonly ImageManager $images) {}

    /**
     * Regenerate from the configured source.
     */
    public function refresh(): void
    {
        $this->generate(app(SeoSettings::class)->search_icon_asset_id ?: app(GeneralSettings::class)->favicon_asset_id);
    }

    public function generate(?int $assetId): void
    {
        $source = null;

        if ($assetId && ($media = Asset::query()->find($assetId)?->file())) {
            $source = $media->getPath();
        }

        $source = $source && is_file($source) ? $source : public_path('images/brand/mark.png');

        if (! is_file($source)) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('favicons');

        foreach (self::SIZES as $size) {
            $image = $this->images->read($source)->contain($size, $size, 'ffffff00');
            $disk->put('favicons/favicon-'.$size.'.png', (string) $image->toPng());
        }

        $disk->put('favicons/favicon.ico', $this->ico((string) $disk->get('favicons/favicon-48.png'), 48));
        $disk->put('favicons/version.txt', (string) time());
    }

    /**
     * A single-image ICO whose payload is a PNG (supported by browsers and crawlers).
     */
    private function ico(string $png, int $size): string
    {
        $header = pack('vvv', 0, 1, 1);
        $entry = pack('CCCCvvVV', $size >= 256 ? 0 : $size, $size >= 256 ? 0 : $size, 0, 0, 1, 32, strlen($png), 6 + 16);

        return $header.$entry.$png;
    }

    /**
     * Path of the generated favicon.ico (created on first use).
     */
    public function icoPath(): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('favicons/favicon.ico')) {
            rescue(fn () => $this->refresh(), report: false);
        }

        return $disk->exists('favicons/favicon.ico') ? $disk->path('favicons/favicon.ico') : null;
    }

    /**
     * @return array<int, string> size => URL (with cache-busting version)
     */
    public static function urls(): array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('favicons/favicon-48.png')) {
            try {
                app(self::class)->refresh();
            } catch (Throwable) {
                return [32 => asset('images/brand/mark-sm.png')];
            }
        }

        if (! $disk->exists('favicons/favicon-48.png')) {
            return [32 => asset('images/brand/mark-sm.png')];
        }

        $version = trim((string) $disk->get('favicons/version.txt'));
        $urls = [];

        foreach (self::SIZES as $size) {
            $urls[$size] = $disk->url('favicons/favicon-'.$size.'.png').'?v='.$version;
        }

        return $urls;
    }
}
