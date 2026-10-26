<?php

namespace App\Livewire\Admin\Content;

use App\Enums\PublishStatus;
use App\Livewire\Admin\Concerns\WithRepeaters;
use App\Livewire\Admin\Concerns\WithSeoForm;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use App\Models\ContentModel;
use App\Support\LinkOptions;
use App\Support\Preview;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Base for full-page content editors (pages, services, projects, posts, jobs):
 * `$form` holds the fields, `$seo` the SEO panel, `$status` / `$published_at`
 * the publish panel. Subclasses describe the model, fields, rules and how to
 * persist relations/assets.
 */
#[Layout('layouts.admin')]
abstract class ContentForm extends Component
{
    use WithRepeaters;
    use WithSeoForm;
    use WithToasts;
    use WithTranslatableForm;

    public ?int $recordId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $status = 'draft';

    public ?string $published_at = null;

    /**
     * @return class-string<ContentModel>
     */
    abstract protected function modelClass(): string;

    /** Route-name prefix, e.g. "admin.projects". */
    abstract protected function routePrefix(): string;

    /** Translation key of the singular item name, e.g. "admin.content.singular.project". */
    abstract protected function moduleLabel(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function defaults(): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function formFromModel(ContentModel $model): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function formRules(): array;

    /**
     * Model attributes from `$form` (relations/assets go in afterSave()).
     *
     * @return array<string, mixed>
     */
    abstract protected function attributesFromForm(): array;

    protected function afterSave(ContentModel $model): void {}

    protected function publishable(): bool
    {
        return true;
    }

    protected function hasSlug(): bool
    {
        return true;
    }

    /** Field used as the human title (title or name). */
    protected function titleField(): string
    {
        return 'title';
    }

    /**
     * Eager loads for the edited record.
     *
     * @return list<string>
     */
    protected function eagerLoads(): array
    {
        return ['seo', 'assets'];
    }

    public function mount(?int $id = null): void
    {
        $class = $this->modelClass();

        if ($id !== null) {
            $record = $class::query()->with($this->eagerLoads())->findOrFail($id);
            $this->authorize('update', $record);
            $this->recordId = $record->getKey();
            $this->form = array_replace_recursive($this->defaults(), $this->formFromModel($record));
            $this->loadSeo($record);

            if ($this->publishable()) {
                $this->status = $record->getAttribute('status')?->value === 'published' ? 'published' : 'draft';
                $this->published_at = $record->getAttribute('published_at')?->format('Y-m-d H:i');
            }
        } else {
            $this->authorize('create', $class);
            $this->form = $this->defaults();
            $this->loadSeo(null);
        }
    }

    public function record(): ?ContentModel
    {
        return $this->recordId ? ($this->modelClass())::query()->with($this->eagerLoads())->find($this->recordId) : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [...$this->formRules(), ...$this->seoRules()];

        if ($this->publishable()) {
            $rules['status'] = ['required', Rule::in(['draft', 'published'])];
            $rules['published_at'] = ['nullable', 'date'];
        }

        if ($this->hasSlug()) {
            $table = (new ($this->modelClass()))->getTable();

            foreach (['ar', 'en'] as $locale) {
                $rules['form.slug.'.$locale] = [
                    'nullable', 'string', 'max:120',
                    Rule::unique($table, 'slug->'.$locale)->ignore($this->recordId),
                ];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'form.'.$this->titleField().'.ar' => __('admin.content.fields.title'),
            'form.slug.ar' => __('validation.attributes.slug'),
            'form.slug.en' => __('validation.attributes.slug'),
        ];
    }

    public function saveDraft(): void
    {
        $this->status = 'draft';
        $this->save();
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->save();
    }

    public function save(): void
    {
        if ($this->hasSlug()) {
            foreach (['ar', 'en'] as $locale) {
                $this->form['slug'][$locale] = $this->cleanSlug($this->form['slug'][$locale] ?? '');
            }
        }

        $this->validate();

        $class = $this->modelClass();
        $model = $this->recordId ? $class::query()->findOrFail($this->recordId) : new $class;
        $this->authorize($model->exists ? 'update' : 'create', $model->exists ? $model : $class);

        DB::transaction(function () use ($model): void {
            $model->fill($this->attributesFromForm());

            if ($this->hasSlug()) {
                $slugs = array_filter(Translate::pair($this->form['slug'] ?? []), fn (string $s) => $s !== '');
                $existing = $model->exists ? $model->getTranslations('slug') : [];
                $model->setTranslations('slug', array_merge($existing, $slugs));

                foreach (['ar', 'en'] as $locale) {
                    if (! isset($slugs[$locale]) && ! $model->exists) {
                        $model->forgetTranslation('slug', $locale);
                    }
                }
            }

            if ($this->publishable()) {
                $model->setAttribute('status', PublishStatus::from($this->status));
                $model->setAttribute('published_at', $this->published_at ? Carbon::parse($this->published_at) : ($this->status === 'published' ? now() : null));
            }

            $model->save();
            $this->afterSave($model);
            $this->saveSeo($model);
        });

        $model->refresh();
        $created = $this->recordId === null;
        $this->recordId = $model->getKey();

        if ($this->hasSlug()) {
            $this->form['slug'] = Translate::pair($model->getTranslations('slug'));
        }

        if ($this->publishable()) {
            $this->published_at = $model->getAttribute('published_at')?->format('Y-m-d H:i');
        }

        $message = $this->status === 'published' && $this->publishable() ? __('admin.content.published_toast') : __('admin.common.saved');
        $this->dispatch('form-saved');

        if ($created) {
            $this->flashToast('success', $message);
            $this->redirectRoute($this->routePrefix().'.edit', ['id' => $model->getKey()], navigate: true);

            return;
        }

        $this->toast('success', $message);
    }

    public function delete(): void
    {
        $model = $this->record();
        abort_if($model === null, 404);
        $this->authorize('delete', $model);
        $model->delete();

        $this->flashToast('success', __('admin.common.deleted'));
        $this->redirectRoute($this->routePrefix().'.index', navigate: true);
    }

    /**
     * Options of the link pickers (computed once per request).
     *
     * @return array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>}
     */
    #[Computed]
    public function linkOptions(): array
    {
        return LinkOptions::all();
    }

    public function previewUrl(string $locale = 'ar'): ?string
    {
        $model = $this->record();

        return $model ? Preview::url($model, $locale) : null;
    }

    /**
     * Translation completeness of the main fields (for the side panel).
     */
    public function englishMissing(): bool
    {
        $values = [];

        foreach ($this->form as $value) {
            if (is_array($value) && array_key_exists('ar', $value) && array_key_exists('en', $value) && ! is_array($value['ar'])) {
                $values[] = $value;
            }
        }

        return $this->missingEnglish($values);
    }

    public function displayStatus(): string
    {
        if (! $this->publishable()) {
            return '';
        }

        if ($this->status === 'published' && $this->published_at && Carbon::parse($this->published_at)->isFuture()) {
            return 'scheduled';
        }

        return $this->status;
    }

    public function pageTitle(): string
    {
        $name = Translate::pick($this->form[$this->titleField()] ?? []);

        if ($this->recordId) {
            return __('admin.content.edit_title', ['module' => __($this->moduleLabel()), 'name' => $name]);
        }

        $own = 'admin.content.create_titles.'.Str::afterLast($this->moduleLabel(), '.');

        return Lang::has($own) ? __($own) : __('admin.content.create_title', ['module' => __($this->moduleLabel())]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    abstract protected function view(): string;

    public function render(): View
    {
        return view($this->view(), $this->viewData())->title($this->pageTitle());
    }
}
