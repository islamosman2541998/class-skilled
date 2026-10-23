<?php

namespace App\Models\Concerns;

use App\Support\Blocks\Blocks;

/**
 * A `blocks` JSON column (see App\Support\Blocks). Blocks are sanitised on
 * save and the assets they reference are mirrored into the `blocks` asset
 * collection so the media library can show where an asset is used.
 *
 * @property array<int, array<string, mixed>>|null $blocks
 */
trait HasBlocks
{
    public function initializeHasBlocks(): void
    {
        $this->mergeCasts(['blocks' => 'array']);
    }

    public static function bootHasBlocks(): void
    {
        static::saving(function (self $model): void {
            if ($model->isDirty('blocks')) {
                $model->blocks = Blocks::sanitize($model->blocks);
            }
        });

        static::saved(function (self $model): void {
            if ($model->wasRecentlyCreated || $model->wasChanged('blocks')) {
                $model->syncAssets('blocks', Blocks::assetIds($model->blocks), touch: false);
            }
        });
    }

    /**
     * @return list<array{id: string, type: string, view: string, data: array<string, mixed>}>
     */
    public function preparedBlocks(?string $locale = null): array
    {
        return Blocks::prepare($this->blocks, $locale);
    }
}
