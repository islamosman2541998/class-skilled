<?php

namespace App\Livewire\Admin\Content\Posts;

use App\Livewire\Admin\Content\ContentForm;
use App\Models\ContentModel;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Support\Translate;

class Form extends ContentForm
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.posts';
    }

    protected function moduleLabel(): string
    {
        return 'admin.content.singular.post';
    }

    protected function view(): string
    {
        return 'livewire.admin.content.posts.form';
    }

    protected function defaults(): array
    {
        return [
            'title' => ['ar' => '', 'en' => ''],
            'slug' => ['ar' => '', 'en' => ''],
            'excerpt' => ['ar' => '', 'en' => ''],
            'body' => ['ar' => '', 'en' => ''],
            'post_category_id' => null,
            'author_id' => auth()->id(),
            'cover' => null,
            'is_featured' => false,
        ];
    }

    protected function formFromModel(ContentModel $model): array
    {
        /** @var Post $model */
        return [
            'title' => Translate::pair($model->getTranslations('title')),
            'slug' => Translate::pair($model->getTranslations('slug')),
            'excerpt' => Translate::pair($model->getTranslations('excerpt')),
            'body' => Translate::pair($model->getTranslations('body')),
            'post_category_id' => $model->post_category_id,
            'author_id' => $model->author_id,
            'cover' => $model->firstAsset('cover')?->id,
            'is_featured' => $model->is_featured,
        ];
    }

    protected function formRules(): array
    {
        return [
            ...$this->pairRules('form.title', true, 160),
            ...$this->pairRules('form.excerpt', false, 400),
            'form.body.ar' => ['required', 'string', 'max:100000'],
            'form.body.en' => ['nullable', 'string', 'max:100000'],
            'form.post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'form.author_id' => ['nullable', 'integer', 'exists:users,id'],
            'form.cover' => ['nullable', 'integer', 'exists:assets,id'],
            'form.is_featured' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [...parent::validationAttributes(), 'form.body.ar' => __('admin.posts.body')];
    }

    protected function attributesFromForm(): array
    {
        return [
            'title' => array_filter(Translate::pair($this->form['title'])),
            'excerpt' => array_filter(Translate::pair($this->form['excerpt'])),
            'body' => array_filter(['ar' => (string) $this->form['body']['ar'], 'en' => (string) $this->form['body']['en']], fn ($v) => trim(strip_tags($v)) !== ''),
            'post_category_id' => $this->form['post_category_id'] ?: null,
            'author_id' => $this->form['author_id'] ?: null,
            'is_featured' => (bool) $this->form['is_featured'],
        ];
    }

    protected function afterSave(ContentModel $model): void
    {
        /** @var Post $model */
        $model->syncAssets('cover', array_filter([$this->form['cover']]), touch: false);
    }

    protected function viewData(): array
    {
        $record = $this->record();

        return [
            'categories' => PostCategory::query()->ordered()->get()->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', 'ar')])->all(),
            'authors' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
            'readingMinutes' => $record instanceof Post ? $record->reading_minutes : null,
            'urls' => ['ar' => $record?->localizedUrl('ar'), 'en' => $record?->localizedUrl('en')],
        ];
    }
}
