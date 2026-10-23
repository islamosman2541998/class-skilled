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
     * Persist a new order for the given ids (a full list or one page of it):
     * the ids take the sort values they already occupied, in the new order.
     *
     * @param  array<int, int|string>  $ids
     */
    public static function saveOrder(array $ids): void
    {
        $ids = array_values(array_map('intval', $ids));

        DB::transaction(function () use ($ids): void {
            $current = static::query()->whereKey($ids)->pluck('sort')->map(fn ($sort) => (int) $sort)->sort()->values()->all();

            if (count($current) !== count($ids) || count(array_unique($current)) !== count($current)) {
                $start = $current === [] ? 1 : max(1, min($current));
                $current = range($start, $start + count($ids) - 1);
            }

            foreach ($ids as $index => $id) {
                static::query()->whereKey($id)->update(['sort' => $current[$index]]);
            }
        });

        app(SiteCache::class)->flush();
    }
}
