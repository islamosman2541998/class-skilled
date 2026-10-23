<?php

namespace App\Models\Concerns;

use App\Services\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Manual ordering through an integer `sort` column.
 *
 * @property int $sort
 */
trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function (self $model): void {
            if (! $model->sort) {
                $model->sort = (int) static::query()->max('sort') + 1;
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort'))->orderBy($this->qualifyColumn('id'));
    }

    /**
     * Persist a new order; the array position becomes the sort value.
     *
     * @param  array<int, int|string>  $ids
     */
    public static function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            foreach (array_values($ids) as $index => $id) {
                static::query()->whereKey($id)->update(['sort' => $index + 1]);
            }
        });

        app(SiteCache::class)->flush();
    }
}
