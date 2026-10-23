<?php

namespace App\Livewire\Admin\Content;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Builder;

class ProjectCategories extends SimpleResource
{
    protected function modelClass(): string
    {
        return ProjectCategory::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.project_categories';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.project_category';
    }

    protected function baseQuery(): Builder
    {
        return ProjectCategory::query()->withCount('projects');
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.categories.name', 'required' => true, 'max' => 120],
            ['key' => 'slug', 'type' => 'slug', 'label' => 'validation.attributes.slug'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.categories.name'],
            ['key' => 'projects_count', 'label' => 'admin.categories.projects_count'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }
}
