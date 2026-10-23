<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Models\ContentModel;
use App\Services\SeoManager;
use App\Support\LinkResolver;
use App\Support\Preview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Slug lookup for public detail pages: the slug must exist in the current
 * locale and the item must be public, unless a signed dashboard preview of
 * that item is active (then it renders with a ribbon and noindex).
 */
trait ResolvesPublicModels
{
    protected bool $preview = false;

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return TModel
     */
    protected function findPublic(Builder $query, string $slug): Model
    {
        $model = $query->where('slug->'.app()->getLocale(), $slug)->first();

        abort_if($model === null, 404);

        $this->preview = ! LinkResolver::isPublic($model);

        abort_if($this->preview && ! Preview::allows($model), 404);

        if ($model instanceof ContentModel) {
            app(SeoManager::class)->forModel($model, $this->preview);
        }

        return $model;
    }
}
