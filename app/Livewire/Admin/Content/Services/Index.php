<?php

namespace App\Livewire\Admin\Content\Services;

use App\Livewire\Admin\Content\ContentIndex;
use App\Models\ContentModel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class Index extends ContentIndex
{
    protected function modelClass(): string
    {
        return Service::class;
    }

    protected function view(): string
    {
        return 'livewire.admin.content.services.index';
    }

    protected function titleKey(): string
    {
        return 'admin.nav.services';
    }

    protected function translatableSearch(): array
    {
        return ['title', 'summary'];
    }

    protected function baseQuery(): Builder
    {
        return Service::query()->with('assets.media')->withCount('projects');
    }

    protected function applyModuleFilters(Builder $query): void
    {
        if (($this->filters['active'] ?? '') !== '') {
            $query->where('is_active', $this->filters['active'] === '1');
        }
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.content.fields.title').' (ar)' => fn (ContentModel $s) => $s->getTranslation('title', 'ar'),
            __('admin.content.fields.title').' (en)' => fn (ContentModel $s) => $s->getTranslation('title', 'en', false),
            __('admin.services.summary') => fn (ContentModel $s) => $s->getTranslation('summary', 'ar', false),
            __('admin.content.active') => fn (ContentModel $s) => $s->getAttribute('is_active') ? 1 : 0,
            __('admin.content.featured') => fn (ContentModel $s) => $s->getAttribute('is_featured') ? 1 : 0,
        ];
    }
}
