<?php

namespace App\Livewire\Admin\Content\Services;

use App\Livewire\Admin\Concerns\WithBlockEditor;
use App\Livewire\Admin\Content\Concerns\PairLists;
use App\Livewire\Admin\Content\ContentForm;
use App\Models\ContentModel;
use App\Models\Faq;
use App\Models\Service;
use App\Support\Translate;

class Form extends ContentForm
{
    use PairLists;
    use WithBlockEditor;

    protected function modelClass(): string
    {
        return Service::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.services';
    }

    protected function moduleLabel(): string
    {
        return 'admin.content.singular.service';
    }

    protected function view(): string
    {
        return 'livewire.admin.content.services.form';
    }

    protected function publishable(): bool
    {
        return false;
    }

    protected function eagerLoads(): array
    {
        return ['seo', 'assets', 'faqs'];
    }

    protected function defaults(): array
    {
        return [
            'title' => ['ar' => '', 'en' => ''],
            'slug' => ['ar' => '', 'en' => ''],
            'summary' => ['ar' => '', 'en' => ''],
            'excerpt' => ['ar' => '', 'en' => ''],
            'cover' => null,
            'gallery' => [],
            'scope' => [],
            'stages' => [],
            'faqs' => [],
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    protected function formFromModel(ContentModel $model): array
    {
        /** @var Service $model */
        $this->loadBlocks($model->blocks);

        return [
            'title' => Translate::pair($model->getTranslations('title')),
            'slug' => Translate::pair($model->getTranslations('slug')),
            'summary' => Translate::pair($model->getTranslations('summary')),
            'excerpt' => Translate::pair($model->getTranslations('excerpt')),
            'cover' => $model->firstAsset('cover')?->id,
            'gallery' => $model->assetsIn('gallery')->pluck('id')->all(),
            'scope' => $this->pairsFromLists($model->scope),
            'stages' => $this->pairsFromLists($model->stages),
            'faqs' => $this->withRepeaterKeys($model->faqs->map(fn (Faq $f) => [
                'id' => $f->id,
                'q' => Translate::pair($f->getTranslations('question')),
                'a' => Translate::pair($f->getTranslations('answer')),
            ])->all()),
            'is_featured' => $model->is_featured,
            'is_active' => $model->is_active,
        ];
    }

    protected function formRules(): array
    {
        return [
            ...$this->pairRules('form.title', true, 160),
            ...$this->pairRules('form.summary', false, 140),
            ...$this->pairRules('form.excerpt', false, 400),
            'form.cover' => ['nullable', 'integer', 'exists:assets,id'],
            'form.gallery' => ['array'],
            'form.gallery.*' => ['integer', 'exists:assets,id'],
            'form.scope.*.ar' => ['nullable', 'string', 'max:200'],
            'form.scope.*.en' => ['nullable', 'string', 'max:200'],
            'form.stages.*.ar' => ['nullable', 'string', 'max:200'],
            'form.stages.*.en' => ['nullable', 'string', 'max:200'],
            'form.faqs.*.q.ar' => ['required', 'string', 'max:300'],
            'form.faqs.*.q.en' => ['nullable', 'string', 'max:300'],
            'form.faqs.*.a.ar' => ['required', 'string', 'max:3000'],
            'form.faqs.*.a.en' => ['nullable', 'string', 'max:3000'],
            'form.is_featured' => ['boolean'],
            'form.is_active' => ['boolean'],
            ...$this->blockRules(),
        ];
    }

    protected function attributesFromForm(): array
    {
        return [
            'title' => array_filter(Translate::pair($this->form['title'])),
            'summary' => array_filter(Translate::pair($this->form['summary'])),
            'excerpt' => array_filter(Translate::pair($this->form['excerpt'])),
            'scope' => $this->listsFromPairs($this->form['scope']),
            'stages' => $this->listsFromPairs($this->form['stages']),
            'blocks' => $this->blocksForSave(),
            'is_featured' => (bool) $this->form['is_featured'],
            'is_active' => (bool) $this->form['is_active'],
        ];
    }

    protected function afterSave(ContentModel $model): void
    {
        /** @var Service $model */
        $model->syncAssets('cover', array_filter([$this->form['cover']]), touch: false);
        $model->syncAssets('gallery', (array) $this->form['gallery'], touch: false);

        $keep = [];

        foreach (array_values((array) $this->form['faqs']) as $index => $row) {
            $faq = isset($row['id']) ? $model->faqs()->find($row['id']) : null;
            $faq ??= new Faq;
            $faq->fill([
                'question' => array_filter(Translate::pair($row['q'])),
                'answer' => array_filter(Translate::pair($row['a'])),
                'is_active' => true,
                'sort' => $index + 1,
            ]);
            $faq->faqable()->associate($model);
            $faq->save();
            $keep[] = $faq->id;
            $this->form['faqs'][$index]['id'] = $faq->id;
        }

        $model->faqs()->whereNotIn('id', $keep)->delete();
    }

    protected function viewData(): array
    {
        $record = $this->record();

        return [
            'urls' => ['ar' => $record?->localizedUrl('ar'), 'en' => $record?->localizedUrl('en')],
            'projectsCount' => $record instanceof Service ? $record->projects()->count() : 0,
        ];
    }
}
