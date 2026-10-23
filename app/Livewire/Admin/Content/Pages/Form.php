<?php

namespace App\Livewire\Admin\Content\Pages;

use App\Enums\HeroMediaType;
use App\Livewire\Admin\Concerns\WithBlockEditor;
use App\Livewire\Admin\Content\ContentForm;
use App\Models\Asset;
use App\Models\ContentModel;
use App\Models\Page;
use App\Support\Translate;
use Illuminate\Validation\Rule;

/**
 * Custom page builder: header (title, slug, description, main media),
 * content blocks, attachments (files → videos → images) and page settings.
 */
class Form extends ContentForm
{
    use WithBlockEditor;

    public const HERO_COLLECTIONS = ['image' => 'hero_image', 'mobile' => 'hero_mobile_image', 'video' => 'hero_video', 'embed' => 'hero_video', 'pdf' => 'hero_pdf'];

    public const GROUPS = ['files' => 'att_files', 'videos' => 'att_videos', 'images' => 'att_images'];

    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.pages';
    }

    protected function moduleLabel(): string
    {
        return 'admin.content.singular.page';
    }

    protected function view(): string
    {
        return 'livewire.admin.content.pages.form';
    }

    protected function defaults(): array
    {
        $group = fn (string $display, ?int $columns = null) => array_filter([
            'visible' => true, 'title' => ['ar' => '', 'en' => ''], 'display' => $display, 'columns' => $columns,
        ], fn ($v) => $v !== null);

        return [
            'title' => ['ar' => '', 'en' => ''],
            'slug' => ['ar' => '', 'en' => ''],
            'excerpt' => ['ar' => '', 'en' => ''],
            'hero_type' => 'none',
            'hero' => ['image' => null, 'mobile' => null, 'video' => null, 'embed' => null, 'pdf' => null],
            'attachments' => ['files' => $group('list'), 'videos' => $group('grid'), 'images' => $group('grid', 3)],
            'att' => ['files' => [], 'videos' => [], 'images' => []],
            'att_titles' => [],
            'parent_id' => null,
            'template' => 'normal',
            'show_in_sitemap' => true,
        ];
    }

    protected function formFromModel(ContentModel $model): array
    {
        /** @var Page $model */
        $this->loadBlocks($model->blocks);

        $hero = [];

        foreach (self::HERO_COLLECTIONS as $key => $collection) {
            $hero[$key] = $model->firstAsset($collection)?->id;
        }

        $att = [];
        $titles = [];

        foreach (self::GROUPS as $group => $collection) {
            $att[$group] = [];

            foreach ($model->assetsIn($collection) as $asset) {
                $att[$group][] = $asset->id;
                $meta = json_decode((string) $asset->getRelationValue('pivot')?->meta, true) ?: [];
                $titles[$asset->id] = Translate::pair($meta['title'] ?? []);
            }
        }

        $attachments = [];

        foreach (array_keys(self::GROUPS) as $group) {
            $attachments[$group] = $model->attachmentGroup($group);
            $attachments[$group]['title'] = Translate::pair($attachments[$group]['title'] ?? []);
        }

        return [
            'title' => Translate::pair($model->getTranslations('title')),
            'slug' => Translate::pair($model->getTranslations('slug')),
            'excerpt' => Translate::pair($model->getTranslations('excerpt')),
            'hero_type' => $model->hero_type->value,
            'hero' => $hero,
            'attachments' => $attachments,
            'att' => $att,
            'att_titles' => $titles,
            'parent_id' => $model->parent_id,
            'template' => $model->template,
            'show_in_sitemap' => $model->show_in_sitemap,
        ];
    }

    protected function formRules(): array
    {
        return [
            ...$this->pairRules('form.title', true, 160),
            ...$this->pairRules('form.excerpt', false, 400),
            'form.hero_type' => ['required', Rule::in(HeroMediaType::values())],
            'form.hero.image' => ['nullable', 'integer', 'exists:assets,id', Rule::requiredIf(($this->form['hero_type'] ?? '') === 'image')],
            'form.hero.mobile' => ['nullable', 'integer', 'exists:assets,id'],
            'form.hero.video' => ['nullable', 'integer', 'exists:assets,id', Rule::requiredIf(($this->form['hero_type'] ?? '') === 'video')],
            'form.hero.embed' => ['nullable', 'integer', 'exists:assets,id', Rule::requiredIf(($this->form['hero_type'] ?? '') === 'embed')],
            'form.hero.pdf' => ['nullable', 'integer', 'exists:assets,id', Rule::requiredIf(($this->form['hero_type'] ?? '') === 'pdf')],
            'form.attachments.*.visible' => ['boolean'],
            'form.attachments.*.title.ar' => ['nullable', 'string', 'max:120'],
            'form.attachments.*.title.en' => ['nullable', 'string', 'max:120'],
            'form.attachments.files.display' => ['in:list'],
            'form.attachments.videos.display' => ['in:grid'],
            'form.attachments.images.display' => ['in:grid,masonry'],
            'form.attachments.images.columns' => ['in:2,3,4'],
            'form.att.*' => ['array'],
            'form.att.*.*' => ['integer', 'exists:assets,id'],
            'form.att_titles.*.ar' => ['nullable', 'string', 'max:200'],
            'form.att_titles.*.en' => ['nullable', 'string', 'max:200'],
            'form.parent_id' => ['nullable', 'integer', 'exists:pages,id', Rule::notIn(array_filter([$this->recordId]))],
            'form.template' => ['required', 'in:normal,wide'],
            'form.show_in_sitemap' => ['boolean'],
            ...$this->blockRules(),
        ];
    }

    protected function attributesFromForm(): array
    {
        $attachments = [];

        foreach (array_keys(self::GROUPS) as $group) {
            $config = (array) ($this->form['attachments'][$group] ?? []);
            $attachments[$group] = [
                'visible' => (bool) ($config['visible'] ?? true),
                'title' => Translate::pair($config['title'] ?? []),
                'display' => (string) ($config['display'] ?? 'grid'),
            ] + (isset($config['columns']) ? ['columns' => (int) $config['columns']] : []);
        }

        return [
            'title' => array_filter(Translate::pair($this->form['title'])),
            'excerpt' => array_filter(Translate::pair($this->form['excerpt'])),
            'hero_type' => $this->form['hero_type'],
            'blocks' => $this->blocksForSave(),
            'attachments_config' => $attachments,
            'parent_id' => $this->form['parent_id'] ?: null,
            'template' => $this->form['template'],
            'show_in_sitemap' => (bool) $this->form['show_in_sitemap'],
        ];
    }

    protected function afterSave(ContentModel $model): void
    {
        /** @var Page $model */
        $hero = (array) $this->form['hero'];
        $type = $this->form['hero_type'];

        $model->syncAssets('hero_image', $type === 'image' ? [$hero['image']] : [], touch: false);
        $model->syncAssets('hero_mobile_image', $type === 'image' ? [$hero['mobile']] : [], touch: false);
        $model->syncAssets('hero_video', match ($type) {
            'video' => [$hero['video']],
            'embed' => [$hero['embed']],
            default => [],
        }, touch: false);
        $model->syncAssets('hero_pdf', $type === 'pdf' ? [$hero['pdf']] : [], touch: false);

        foreach (self::GROUPS as $group => $collection) {
            $ids = array_values(array_map('intval', (array) ($this->form['att'][$group] ?? [])));
            $meta = [];

            foreach ($ids as $id) {
                $title = array_filter(Translate::pair($this->form['att_titles'][$id] ?? []));

                if ($title !== []) {
                    $meta[$id] = ['title' => $title];
                }
            }

            $model->syncAssets($collection, $ids, $meta, touch: false);
        }
    }

    public function updatedForm(mixed $value, string $key): void
    {
        if (! str_starts_with($key, 'att.')) {
            return;
        }

        foreach ((array) $this->form['att'] as $ids) {
            foreach ((array) $ids as $id) {
                $this->form['att_titles'][$id] ??= ['ar' => '', 'en' => ''];
            }
        }
    }

    protected function viewData(): array
    {
        $record = $this->record();

        return [
            'parents' => Page::query()->when($this->recordId, fn ($q) => $q->whereKeyNot($this->recordId))
                ->orderBy('sort')->get()->mapWithKeys(fn (Page $p) => [$p->id => $p->getTranslation('title', 'ar')])->all(),
            'urls' => ['ar' => $record?->localizedUrl('ar'), 'en' => $record?->localizedUrl('en')],
            'assetTitles' => Asset::query()->whereIn('id', collect($this->form['att'])->flatten()->all())->get()->mapWithKeys(fn ($a) => [$a->id => $a->titleText()])->all(),
        ];
    }
}
