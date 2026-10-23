<?php

namespace App\Livewire\Admin\Content\Projects;

use App\Livewire\Admin\Content\ContentIndex;
use App\Models\ContentModel;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Builder;

class Index extends ContentIndex
{
    protected function modelClass(): string
    {
        return Project::class;
    }

    protected function view(): string
    {
        return 'livewire.admin.content.projects.index';
    }

    protected function titleKey(): string
    {
        return 'admin.nav.projects';
    }

    protected function eagerLoads(): array
    {
        return ['category', 'assets.media'];
    }

    protected function applyModuleFilters(Builder $query): void
    {
        if (filled($this->filters['category'] ?? null)) {
            $query->where('category_id', (int) $this->filters['category']);
        }

        if (filled($this->filters['year'] ?? null)) {
            $query->where('year', (int) $this->filters['year']);
        }
    }

    protected function sortableColumns(): array
    {
        return [...parent::sortableColumns(), 'year' => 'year'];
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.content.fields.title').' (ar)' => fn (ContentModel $p) => $p->getTranslation('title', 'ar'),
            __('admin.content.fields.title').' (en)' => fn (ContentModel $p) => $p->getTranslation('title', 'en', false),
            __('admin.projects.category') => fn (ContentModel $p) => $p->getRelationValue('category')?->getTranslation('name', 'ar'),
            __('admin.projects.location') => fn (ContentModel $p) => $p->getTranslation('location', 'ar', false),
            __('admin.projects.year') => fn (ContentModel $p) => $p->getAttribute('year'),
            __('admin.common.status') => fn (ContentModel $p) => $p->getAttribute('status')?->label(),
        ];
    }

    protected function viewData(): array
    {
        return [
            'categories' => ProjectCategory::query()->ordered()->get()->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', 'ar')])->all(),
            'years' => Project::query()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year', 'year')->all(),
        ];
    }
}
