<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

/**
 * Builds favicon PNGs (32, 180, 192, 512) with GD from the favicon asset into
 * the public disk (favicons/). Falls back to the bundled brand mark.
 */
class FaviconGenerator
{
    public const SIZES = [32, 180, 192, 512];

    public function __construct(private readonly ImageManager $images) {}

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

        $disk->put('favicons/version.txt', (string) time());
    }

    /**
     * @return array<int, string> size => URL (with cache-busting version)
     */
    public static function urls(): array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('favicons/favicon-32.png')) {
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
