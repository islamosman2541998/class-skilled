<?php

namespace App\Livewire\Admin\Content\Jobs;

use App\Enums\JobType;
use App\Livewire\Admin\Content\Concerns\PairLists;
use App\Livewire\Admin\Content\ContentForm;
use App\Models\ContentModel;
use App\Models\JobPost;
use App\Support\Translate;
use Illuminate\Validation\Rule;

class Form extends ContentForm
{
    use PairLists;

    protected function modelClass(): string
    {
        return JobPost::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.jobs';
    }

    protected function moduleLabel(): string
    {
        return 'admin.content.singular.job';
    }

    protected function view(): string
    {
        return 'livewire.admin.content.jobs.form';
    }

    protected function eagerLoads(): array
    {
        return ['seo'];
    }

    protected function defaults(): array
    {
        return [
            'title' => ['ar' => '', 'en' => ''],
            'slug' => ['ar' => '', 'en' => ''],
            'department' => ['ar' => '', 'en' => ''],
            'location' => ['ar' => 'أبوظبي', 'en' => 'Abu Dhabi'],
            'job_type' => JobType::FullTime->value,
            'description' => ['ar' => '', 'en' => ''],
            'requirements' => [],
            'closes_at' => null,
        ];
    }

    protected function formFromModel(ContentModel $model): array
    {
        /** @var JobPost $model */
        return [
            'title' => Translate::pair($model->getTranslations('title')),
            'slug' => Translate::pair($model->getTranslations('slug')),
            'department' => Translate::pair($model->getTranslations('department')),
            'location' => Translate::pair($model->getTranslations('location')),
            'job_type' => $model->job_type->value,
            'description' => Translate::pair($model->getTranslations('description')),
            'requirements' => $this->pairsFromLists($model->requirements),
            'closes_at' => $model->closes_at?->format('Y-m-d'),
        ];
    }

    protected function formRules(): array
    {
        return [
            ...$this->pairRules('form.title', true, 160),
            ...$this->pairRules('form.department', false, 120),
            ...$this->pairRules('form.location', false, 120),
            'form.job_type' => ['required', Rule::in(JobType::values())],
            'form.description.ar' => ['required', 'string', 'max:50000'],
            'form.description.en' => ['nullable', 'string', 'max:50000'],
            'form.requirements.*.ar' => ['nullable', 'string', 'max:300'],
            'form.requirements.*.en' => ['nullable', 'string', 'max:300'],
            'form.closes_at' => ['nullable', 'date'],
        ];
    }

    protected function attributesFromForm(): array
    {
        return [
            'title' => array_filter(Translate::pair($this->form['title'])),
            'department' => array_filter(Translate::pair($this->form['department'])),
            'location' => array_filter(Translate::pair($this->form['location'])),
            'job_type' => $this->form['job_type'],
            'description' => array_filter(['ar' => (string) $this->form['description']['ar'], 'en' => (string) $this->form['description']['en']], fn ($v) => trim(strip_tags($v)) !== ''),
            'requirements' => $this->listsFromPairs($this->form['requirements']),
            'closes_at' => $this->form['closes_at'] ?: null,
        ];
    }

    protected function viewData(): array
    {
        $record = $this->record();

        return [
            'urls' => ['ar' => $record?->localizedUrl('ar'), 'en' => $record?->localizedUrl('en')],
            'applications' => $record instanceof JobPost ? $record->applications()->count() : 0,
        ];
    }
}
