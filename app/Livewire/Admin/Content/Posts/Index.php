<?php

namespace App\Livewire\Admin\Content\Posts;

use App\Livewire\Admin\Content\ContentIndex;
use App\Models\ContentModel;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;

class Index extends ContentIndex
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function view(): string
    {
        return 'livewire.admin.content.posts.index';
    }

    protected function titleKey(): string
    {
        return 'admin.nav.posts';
    }

    protected function eagerLoads(): array
    {
        return ['category', 'assets.media'];
    }

    protected function applyModuleFilters(Builder $query): void
    {
        if (filled($this->filters['category'] ?? null)) {
            $query->where('post_category_id', (int) $this->filters['category']);
        }
    }

    protected function defaultSort(): array
    {
        return ['published', 'desc'];
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.content.fields.title').' (ar)' => fn (ContentModel $p) => $p->getTranslation('title', 'ar'),
            __('admin.content.fields.title').' (en)' => fn (ContentModel $p) => $p->getTranslation('title', 'en', false),
            __('admin.posts.category') => fn (ContentModel $p) => $p->getRelationValue('category')?->getTranslation('name', 'ar'),
            __('admin.content.published_at') => fn (ContentModel $p) => $p->getAttribute('published_at')?->format('Y-m-d H:i'),
            __('admin.common.status') => fn (ContentModel $p) => $p->getAttribute('status')?->label(),
        ];
    }

    protected function viewData(): array
    {
        return [
            'categories' => PostCategory::query()->ordered()->get()->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', 'ar')])->all(),
        ];
    }
}
