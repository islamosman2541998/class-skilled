<?php

namespace App\Models\Concerns;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

/**
 * Attach library assets to a model in named, ordered collections
 * (pivot `assetables`: collection, sort, meta json).
 *
 * Eager load with `->with('assets.media')`; the helpers then filter the loaded
 * relation instead of querying again.
 *
 * @property-read Collection<int, Asset> $assets
 */
trait HasAssets
{
    public static function bootHasAssets(): void
    {
        static::deleting(function (self $model): void {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->assets()->detach();
        });
    }

    /**
     * @return MorphToMany<Asset, $this>
     */
    public function assets(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'assetable', 'assetables')
            ->withPivot(['id', 'collection', 'sort', 'meta'])
            ->orderBy('assetables.sort');
    }

    /**
     * @return Collection<int, Asset>
     */
    public function assetsIn(string $collection): Collection
    {
        if ($this->relationLoaded('assets')) {
            return $this->assets
                ->filter(fn (Asset $asset) => $asset->getRelationValue('pivot')?->collection === $collection)
                ->values();
        }

        return $this->assets()->wherePivot('collection', $collection)->with('media')->get();
    }

    public function firstAsset(string $collection): ?Asset
    {
        return $this->assetsIn($collection)->first();
    }

    /**
     * Replace the assets of one collection, keeping the given order.
     *
     * @param  array<int, int|string>  $ids
     * @param  array<int|string, array<string, mixed>>  $meta  keyed by asset id
     * @param  bool  $touch  false when called from the owner's own save events
     */
    public function syncAssets(string $collection, array $ids, array $meta = [], bool $touch = true): void
    {
        $ids = array_values(array_unique(array_map('intval', array_filter($ids))));

        DB::transaction(function () use ($collection, $ids, $meta): void {
            $this->assets()->wherePivot('collection', $collection)->detach();

            foreach ($ids as $index => $id) {
                $this->assets()->attach($id, [
                    'collection' => $collection,
                    'sort' => $index + 1,
                    'meta' => isset($meta[$id]) ? json_encode($meta[$id], JSON_UNESCAPED_UNICODE) : null,
                ]);
            }
        });

        $this->unsetRelation('assets');

        if ($touch) {
            $this->touchForAssets();
        }
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function attachAsset(Asset|int $asset, string $collection, ?array $meta = null): void
    {
        $id = $asset instanceof Asset ? $asset->getKey() : $asset;

        if ($this->assets()->wherePivot('collection', $collection)->whereKey($id)->exists()) {
            return;
        }

        $sort = (int) DB::table('assetables')
            ->where('assetable_type', $this->getMorphClass())
            ->where('assetable_id', $this->getKey())
            ->where('collection', $collection)
            ->max('sort');

        $this->assets()->attach($id, [
            'collection' => $collection,
            'sort' => $sort + 1,
            'meta' => $meta === null ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);

        $this->unsetRelation('assets');
        $this->touchForAssets();
    }

    public function detachAsset(Asset|int $asset, ?string $collection = null): void
    {
        $id = $asset instanceof Asset ? $asset->getKey() : $asset;
        $query = $this->assets();

        if ($collection !== null) {
            $query->wherePivot('collection', $collection);
        }

        $query->detach($id);
        $this->unsetRelation('assets');
        $this->touchForAssets();
    }

    /**
     * Pivot writes do not fire model events; bump the owner so caches and
     * observers see the change.
     */
    protected function touchForAssets(): void
    {
        if ($this->exists && $this->usesTimestamps()) {
            $this->touch();
        }
    }
}
