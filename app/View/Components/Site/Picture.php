<?php

namespace App\View\Components\Site;

use App\Models\Asset;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Responsive image from a library asset:
 * `<x-site.picture :asset="$asset" sizes="(min-width: 1024px) 33vw, 100vw" class="..." :eager="true" :mobile="$mobileAsset" />`
 *
 * Renders a `<picture>` with WebP `srcset` (sm/md/lg/xl), width/height,
 * lazy loading unless eager, alt text in the current locale and the focal
 * point as `object-position`. Renders nothing when the asset has no image.
 */
class Picture extends Component
{
    public function __construct(
        public ?Asset $asset,
        public string $sizes = '100vw',
        public string $class = '',
        public bool $eager = false,
        public ?Asset $mobile = null,
        public ?string $alt = null,
        public string $fallback = 'lg',
    ) {}

    public function shouldRender(): bool
    {
        return $this->asset !== null && $this->asset->isImage() && $this->asset->file() !== null;
    }

    /**
     * @return string `url 640w, url 1024w, …` (deduplicated by real width)
     */
    public function srcset(Asset $asset): string
    {
        $entries = [];
        $media = $asset->file();

        foreach (array_keys(Asset::WIDTHS) as $conversion) {
            if ($media === null || ! $media->hasGeneratedConversion($conversion)) {
                continue;
            }

            $width = $asset->conversionWidth($conversion);
            $entries[$width] = $media->getUrl($conversion).' '.$width.'w';
        }

        ksort($entries);

        return implode(', ', $entries);
    }

    public function objectPosition(Asset $asset): string
    {
        return match ($asset->focal) {
            'top' => 'center top',
            'bottom' => 'center bottom',
            default => 'center center',
        };
    }

    public function render(): View
    {
        return view('components.site.picture');
    }
}
