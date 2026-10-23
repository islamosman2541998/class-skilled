<?php

namespace App\Livewire\Admin\Requests\Applications;

use App\Enums\RequestType;
use App\Livewire\Admin\Requests\RequestIndex;
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Index extends RequestIndex
{
    protected function modelClass(): string
    {
        return JobApplication::class;
    }

    public function type(): RequestType
    {
        return RequestType::Application;
    }

    public function routeKey(): string
    {
        return 'applications';
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with('jobPost');
    }

    public function extraFilters(): array
    {
        return [
            'job' => [(string) __('admin.requests.job'), ['general' => (string) __('admin.requests.general_application')] + JobPost::query()->latest()->get()->mapWithKeys(fn (JobPost $job) => [(string) $job->id => (string) $job->getTranslation('title', 'ar')])->all()],
        ];
    }

    protected function applyTypeFilters(Builder $query): void
    {
        $job = $this->filters['job'] ?? null;

        if ($job === 'general') {
            $query->whereNull('job_post_id');
        } elseif (filled($job)) {
            $query->where('job_post_id', (int) $job);
        }
    }

    public function extraColumns(): array
    {
        return [
            'job' => [__('admin.requests.job'), fn (Model $row) => $row->getRelationValue('jobPost')?->getTranslation('title', app()->getLocale()) ?? __('admin.requests.general_application')],
            'specialty' => [__('admin.requests.specialty'), fn (Model $row) => $row->getAttribute('specialty')],
        ];
    }
}
