<?php

namespace App\Livewire\Admin\Content;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;

class PostCategories extends SimpleResource
{
    protected function modelClass(): string
    {
        return PostCategory::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.post_categories';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.post_category';
    }

    protected function baseQuery(): Builder
    {
        return PostCategory::query()->withCount('posts');
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.categories.name', 'required' => true, 'max' => 120],
            ['key' => 'slug', 'type' => 'slug', 'label' => 'validation.attributes.slug'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.categories.name'],
            ['key' => 'posts_count', 'label' => 'admin.categories.posts_count'],
        ];
    }
}
