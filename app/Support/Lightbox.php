<?php

namespace App\Support;

use App\Enums\AssetKind;
use App\Models\Asset;
use Illuminate\Support\Collection;

/**
 * JSON items for the site lightbox (`resources/js/site/lightbox.js`).
 */
class Lightbox
{
    /**
     * @param  iterable<Asset>  $assets
     * @return list<array{type: string, src: string, alt: string, caption: string, poster: string|null}>
     */
    public static function items(iterable $assets): array
    {
        $items = [];

        foreach ($assets as $asset) {
            $src = match ($asset->kind) {
                AssetKind::Image => $asset->url('xl') ?: $asset->url(),
                AssetKind::Video => $asset->url(),
                AssetKind::Embed => $asset->embedUrl(),
                default => null,
            };

            if (! $src) {
                continue;
            }

            $items[] = [
                'type' => match ($asset->kind) {
                    AssetKind::Video => 'video',
                    AssetKind::Embed => 'embed',
                    default => 'image',
                },
                'src' => $src,
                'alt' => $asset->kind === AssetKind::Image ? $asset->altText() : $asset->titleText(),
                'caption' => $asset->captionText(),
                'poster' => static::poster($asset),
            ];
        }

        return $items;
    }

    public static function poster(Asset $asset): ?string
    {
        if ($asset->kind === AssetKind::Image) {
            return $asset->url('md');
        }

        if ($asset->poster) {
            return $asset->poster->url('md');
        }

        return $asset->kind === AssetKind::Embed && $asset->provider?->value === 'youtube'
            ? 'https://i.ytimg.com/vi/'.$asset->external_id.'/hqdefault.jpg'
            : null;
    }

    /**
     * @param  Collection<int, Asset>  $assets
     * @return Collection<int, Asset>
     */
    public static function playable(Collection $assets): Collection
    {
        return $assets->filter(fn (Asset $asset) => in_array($asset->kind, [AssetKind::Video, AssetKind::Embed], true))->values();
    }
}
