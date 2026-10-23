<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * LIKE search helpers that also cover translatable JSON columns (`{ar, en}`).
 */
class Search
{
    /**
     * Add OR conditions for every field; translatable fields are searched in both locales.
     *
     * @template TBuilder of Builder<covariant Model>
     *
     * @param  TBuilder  $query
     * @param  list<string>  $fields
     * @param  list<string>  $translatable
     * @return TBuilder
     */
    public static function apply(Builder $query, string $term, array $fields = [], array $translatable = []): Builder
    {
        $like = '%'.addcslashes(trim($term), '%_\\').'%';

        return $query->where(function (Builder $q) use ($like, $fields, $translatable): void {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', $like);
            }

            foreach ($translatable as $field) {
                $q->orWhere($field.'->ar', 'like', $like)->orWhere($field.'->en', 'like', $like);
            }
        });
    }
}
