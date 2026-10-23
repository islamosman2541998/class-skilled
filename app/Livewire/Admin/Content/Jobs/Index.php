<?php

namespace App\Livewire\Admin\Content\Jobs;

use App\Livewire\Admin\Content\ContentIndex;
use App\Models\ContentModel;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;

class Index extends ContentIndex
{
    protected function modelClass(): string
    {
        return JobPost::class;
    }

    protected function view(): string
    {
        return 'livewire.admin.content.jobs.index';
    }

    protected function titleKey(): string
    {
        return 'admin.nav.jobs';
    }

    protected function baseQuery(): Builder
    {
        return JobPost::query()->withCount('applications');
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.content.fields.title').' (ar)' => fn (ContentModel $j) => $j->getTranslation('title', 'ar'),
            __('admin.jobs.department') => fn (ContentModel $j) => $j->getTranslation('department', 'ar', false),
            __('admin.jobs.type') => fn (ContentModel $j) => $j->getAttribute('job_type')?->label(),
            __('admin.jobs.closes_at') => fn (ContentModel $j) => $j->getAttribute('closes_at')?->format('Y-m-d'),
            __('admin.common.status') => fn (ContentModel $j) => $j->getAttribute('status')?->label(),
        ];
    }
}
