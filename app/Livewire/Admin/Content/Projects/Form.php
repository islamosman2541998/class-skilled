<?php

namespace App\Livewire\Admin\Content\Projects;

use App\Enums\ProjectStatus;
use App\Livewire\Admin\Concerns\WithBlockEditor;
use App\Livewire\Admin\Content\Concerns\PairLists;
use App\Livewire\Admin\Content\ContentForm;
use App\Models\ContentModel;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Support\Translate;
use Illuminate\Validation\Rule;

class Form extends ContentForm
{
    use PairLists;
    use WithBlockEditor;

    protected function modelClass(): string
    {
        return Project::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.projects';
    }

    protected function moduleLabel(): string
    {
        return 'admin.content.singular.project';
    }

    protected function view(): string
    {
        return 'livewire.admin.content.projects.form';
    }

    protected function eagerLoads(): array
    {
        return ['seo', 'assets', 'services'];
    }

    protected function defaults(): array
    {
        return [
            'title' => ['ar' => '', 'en' => ''],
            'slug' => ['ar' => '', 'en' => ''],
            'excerpt' => ['ar' => '', 'en' => ''],
            'category_id' => null,
            'service_ids' => [],
            'location' => ['ar' => '', 'en' => ''],
            'area_m2' => null,
            'duration' => ['ar' => '', 'en' => ''],
            'year' => (int) date('Y'),
            'project_status' => ProjectStatus::Completed->value,
            'client_name' => ['ar' => '', 'en' => ''],
            'show_client' => false,
            'cover' => null,
            'gallery' => [],
            'videos' => [],
            'pairs' => [],
            'scope' => [],
            'is_featured' => false,
        ];
    }

    protected function formFromModel(ContentModel $model): array
    {
        /** @var Project $model */
        $this->loadBlocks($model->blocks);
        $pairs = [];

        foreach ($model->beforeAfterPairs() as $pair) {
            $pairs[] = ['before' => $pair['before']->id, 'after' => $pair['after']->id];
        }

        return [
            'title' => Translate::pair($model->getTranslations('title')),
            'slug' => Translate::pair($model->getTranslations('slug')),
            'excerpt' => Translate::pair($model->getTranslations('excerpt')),
            'category_id' => $model->category_id,
            'service_ids' => $model->services->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'location' => Translate::pair($model->getTranslations('location')),
            'area_m2' => $model->area_m2 !== null ? (float) $model->area_m2 : null,
            'duration' => Translate::pair($model->getTranslations('duration')),
            'year' => $model->year,
            'project_status' => $model->project_status->value,
            'client_name' => Translate::pair($model->getTranslations('client_name')),
            'show_client' => $model->show_client,
            'cover' => $model->firstAsset('cover')?->id,
            'gallery' => $model->assetsIn('gallery')->pluck('id')->all(),
            'videos' => $model->assetsIn('videos')->pluck('id')->all(),
            'pairs' => $this->withRepeaterKeys($pairs),
            'scope' => $this->pairsFromLists($model->scope),
            'is_featured' => $model->is_featured,
        ];
    }

    protected function formRules(): array
    {
        return [
            ...$this->pairRules('form.title', true, 160),
            ...$this->pairRules('form.excerpt', false, 400),
            ...$this->pairRules('form.location', false, 120),
            ...$this->pairRules('form.duration', false, 60),
            ...$this->pairRules('form.client_name', false, 120),
            'form.category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'form.service_ids' => ['array'],
            'form.service_ids.*' => ['integer', 'exists:services,id'],
            'form.area_m2' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.year' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 2)],
            'form.project_status' => ['required', Rule::in(ProjectStatus::values())],
            'form.show_client' => ['boolean'],
            'form.cover' => ['nullable', 'integer', 'exists:assets,id'],
            'form.gallery.*' => ['integer', 'exists:assets,id'],
            'form.videos.*' => ['integer', 'exists:assets,id'],
            'form.pairs.*.before' => ['required', 'integer', 'exists:assets,id'],
            'form.pairs.*.after' => ['required', 'integer', 'exists:assets,id'],
            'form.scope.*.ar' => ['nullable', 'string', 'max:200'],
            'form.scope.*.en' => ['nullable', 'string', 'max:200'],
            'form.is_featured' => ['boolean'],
            ...$this->blockRules(),
        ];
    }

    protected function attributesFromForm(): array
    {
        return [
            'title' => array_filter(Translate::pair($this->form['title'])),
            'excerpt' => array_filter(Translate::pair($this->form['excerpt'])),
            'location' => array_filter(Translate::pair($this->form['location'])),
            'duration' => array_filter(Translate::pair($this->form['duration'])),
            'client_name' => array_filter(Translate::pair($this->form['client_name'])),
            'category_id' => $this->form['category_id'] ?: null,
            'area_m2' => filled($this->form['area_m2']) ? $this->form['area_m2'] : null,
            'year' => filled($this->form['year']) ? (int) $this->form['year'] : null,
            'project_status' => $this->form['project_status'],
            'show_client' => (bool) $this->form['show_client'],
            'scope' => $this->listsFromPairs($this->form['scope']),
            'blocks' => $this->blocksForSave(),
            'is_featured' => (bool) $this->form['is_featured'],
        ];
    }

    protected function afterSave(ContentModel $model): void
    {
        /** @var Project $model */
        $model->services()->sync(array_map('intval', (array) $this->form['service_ids']));
        $model->syncAssets('cover', array_filter([$this->form['cover']]), touch: false);
        $model->syncAssets('gallery', (array) $this->form['gallery'], touch: false);
        $model->syncAssets('videos', (array) $this->form['videos'], touch: false);

        // before/after: one pivot row per image with {pair, side}; an image used twice keeps its first pair.
        $ids = [];
        $meta = [];

        foreach (array_values((array) $this->form['pairs']) as $index => $pair) {
            foreach (['before', 'after'] as $side) {
                $id = (int) ($pair[$side] ?? 0);

                if ($id > 0 && ! isset($meta[$id])) {
                    $ids[] = $id;
                    $meta[$id] = ['pair' => $index, 'side' => $side];
                }
            }
        }

        $model->syncAssets('before_after', $ids, $meta, touch: false);
    }

    protected function viewData(): array
    {
        $record = $this->record();

        return [
            'categories' => ProjectCategory::query()->ordered()->get()->mapWithKeys(fn (ProjectCategory $c) => [$c->id => $c->getTranslation('name', 'ar')])->all(),
            'services' => Service::query()->ordered()->get()->mapWithKeys(fn (Service $s) => [$s->id => $s->getTranslation('title', 'ar')])->all(),
            'urls' => ['ar' => $record?->localizedUrl('ar'), 'en' => $record?->localizedUrl('en')],
        ];
    }
}
