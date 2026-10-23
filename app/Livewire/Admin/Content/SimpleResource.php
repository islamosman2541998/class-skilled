<?php

namespace App\Livewire\Admin\Content;

use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use App\Models\ContentModel;
use App\Support\Search;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Schema-driven list + modal form for small content types (FAQs, testimonials,
 * clients, team, certificates, categories). `fields()` describes the form,
 * `columns()` the table; both reuse the admin component kit.
 *
 * Field types: pair, pair_textarea, text, url, number, date, toggle, select, rating, asset, slug.
 */
#[Layout('layouts.admin')]
abstract class SimpleResource extends Component
{
    use WithDataTable;
    use WithToasts;
    use WithTranslatableForm;

    public ?int $editingId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /**
     * @return class-string<ContentModel>
     */
    abstract protected function modelClass(): string;

    abstract public function titleKey(): string;

    abstract public function singularKey(): string;

    /**
     * @return list<array<string, mixed>>
     */
    abstract public function fields(): array;

    /**
     * @return list<array<string, mixed>>
     */
    abstract public function columns(): array;

    /** Translatable field used as the row title (search, delete text, missing English). */
    public function titleField(): string
    {
        return 'name';
    }

    public function mount(): void
    {
        $this->authorize('viewAny', $this->modelClass());
        $this->form = $this->blank();
    }

    protected function baseQuery(): Builder
    {
        return ($this->modelClass())::query()->with($this->eagerLoads());
    }

    /**
     * @return list<string>
     */
    protected function eagerLoads(): array
    {
        return collect($this->fields())->contains('type', 'asset') ? ['assets.media'] : [];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        Search::apply($query, $term, [], [$this->titleField()]);
    }

    protected function applyFilters(Builder $query): void
    {
        if (($this->filters['missing'] ?? '') === '1') {
            $field = $this->titleField();
            $query->where(fn (Builder $q) => $q->whereNull($field.'->en')->orWhere($field.'->en', ''));
        }

        $this->applyModuleFilters($query);
    }

    protected function applyModuleFilters(Builder $query): void {}

    protected function sortableColumns(): array
    {
        return ['sort' => 'sort', 'created' => 'created_at'];
    }

    protected function defaultSort(): array
    {
        return ['sort', 'asc'];
    }

    public function canReorder(): bool
    {
        return $this->currentSort() === ['sort', 'asc'] && $this->search === '' && $this->filters === [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function blank(): array
    {
        $form = [];

        foreach ($this->fields() as $field) {
            $form[$field['key']] = match ($field['type']) {
                'pair', 'pair_textarea', 'slug' => ['ar' => '', 'en' => ''],
                'toggle' => $field['default'] ?? true,
                default => $field['default'] ?? null,
            };
        }

        return $form;
    }

    public function create(): void
    {
        $this->authorize('create', $this->modelClass());
        $this->editingId = null;
        $this->form = $this->blank();
        $this->resetErrorBag();
        $this->dispatch('open-modal', name: 'resource-form');
    }

    public function edit(int $id): void
    {
        $model = ($this->modelClass())::query()->with($this->eagerLoads())->findOrFail($id);
        $this->authorize('update', $model);
        $this->editingId = $model->getKey();
        $form = $this->blank();

        foreach ($this->fields() as $field) {
            $key = $field['key'];
            $form[$key] = match ($field['type']) {
                'pair', 'pair_textarea', 'slug' => Translate::pair($model->getTranslations($key)),
                'asset' => method_exists($model, 'firstAsset') ? $model->firstAsset($field['collection'])?->id : null,
                'date' => $model->getAttribute($key)?->format('Y-m-d'),
                'toggle' => (bool) $model->getAttribute($key),
                default => array_key_exists($key, $model->getAttributes()) ? $model->getAttribute($key) : null,
            };
        }

        $this->form = $this->fillExtra($model, $form);
        $this->resetErrorBag();
        $this->dispatch('open-modal', name: 'resource-form');
    }

    /**
     * Hook for module-specific form values (e.g. FAQ service link).
     *
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    protected function fillExtra(ContentModel $model, array $form): array
    {
        return $form;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [];
        $table = (new ($this->modelClass()))->getTable();

        foreach ($this->fields() as $field) {
            $key = 'form.'.$field['key'];
            $required = (bool) ($field['required'] ?? false);
            $max = (int) ($field['max'] ?? 255);

            switch ($field['type']) {
                case 'pair':
                case 'pair_textarea':
                    $rules += $this->pairRules($key, $required, $max);
                    break;
                case 'slug':
                    foreach (['ar', 'en'] as $locale) {
                        $rules[$key.'.'.$locale] = ['nullable', 'string', 'max:120', Rule::unique($table, 'slug->'.$locale)->ignore($this->editingId)];
                    }
                    break;
                case 'url':
                    $rules[$key] = [$required ? 'required' : 'nullable', 'url', 'max:500'];
                    break;
                case 'number':
                case 'rating':
                    $rules[$key] = [$required ? 'required' : 'nullable', 'integer', 'min:'.($field['min'] ?? 0), 'max:'.($field['maxValue'] ?? 999999)];
                    break;
                case 'date':
                    $rules[$key] = [$required ? 'required' : 'nullable', 'date'];
                    break;
                case 'toggle':
                    $rules[$key] = ['boolean'];
                    break;
                case 'select':
                    $rules[$key] = [$required ? 'required' : 'nullable', Rule::in(array_map('strval', array_keys($field['options'] ?? [])))];
                    break;
                case 'asset':
                    $rules[$key] = [$required ? 'required' : 'nullable', 'integer', 'exists:assets,id'];
                    break;
                default:
                    $rules[$key] = [$required ? 'required' : 'nullable', 'string', 'max:'.$max];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->fields() as $field) {
            $attributes['form.'.$field['key']] = __($field['label']);
            $attributes['form.'.$field['key'].'.ar'] = __($field['label']);
        }

        return $attributes;
    }

    public function save(): void
    {
        foreach ($this->fields() as $field) {
            if ($field['type'] === 'slug') {
                foreach (['ar', 'en'] as $locale) {
                    $this->form[$field['key']][$locale] = $this->cleanSlug($this->form[$field['key']][$locale] ?? '');
                }
            }
        }

        $this->validate();

        $class = $this->modelClass();
        $model = $this->editingId ? $class::query()->findOrFail($this->editingId) : new $class;
        $this->authorize($model->exists ? 'update' : 'create', $model->exists ? $model : $class);

        DB::transaction(function () use ($model): void {
            foreach ($this->fields() as $field) {
                $key = $field['key'];
                $value = $this->form[$key] ?? null;

                switch ($field['type']) {
                    case 'pair':
                    case 'pair_textarea':
                        $model->setAttribute($key, array_filter(Translate::pair($value)));
                        break;
                    case 'slug':
                        $slugs = array_filter(Translate::pair($value));
                        $model->setTranslations('slug', array_merge($model->exists ? $model->getTranslations('slug') : [], $slugs));
                        break;
                    case 'toggle':
                        $model->setAttribute($key, (bool) $value);
                        break;
                    case 'number':
                    case 'rating':
                        $model->setAttribute($key, filled($value) ? (int) $value : null);
                        break;
                    case 'asset':
                        break;
                    default:
                        $model->setAttribute($key, filled($value) ? $value : null);
                }
            }

            $this->beforeSave($model);
            $model->save();

            foreach ($this->fields() as $field) {
                if ($field['type'] === 'asset' && method_exists($model, 'syncAssets')) {
                    $model->syncAssets($field['collection'], array_filter([$this->form[$field['key']] ?? null]));
                }
            }
        });

        $this->editingId = null;
        unset($this->rows);
        $this->dispatch('close-modal', name: 'resource-form');
        $this->toast('success', __('admin.common.saved'));
    }

    protected function beforeSave(ContentModel $model): void {}

    public function toggle(int $id, string $field): void
    {
        $model = ($this->modelClass())::query()->findOrFail($id);
        $this->authorize('update', $model);

        if (! in_array($field, ['is_active', 'show_on_site', 'is_featured'], true)) {
            return;
        }

        $model->setAttribute($field, ! $model->getAttribute($field));
        $model->save();
        unset($this->rows);
        $this->toast('success', __('admin.common.updated'));
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        $class = $this->modelClass();
        $this->authorize('update', new $class);
        $class::saveOrder($ids);
        unset($this->rows);
        $this->toast('success', __('admin.common.reordered'));
    }

    public function delete(int $id): void
    {
        $model = ($this->modelClass())::query()->findOrFail($id);
        $this->authorize('delete', $model);
        $model->delete();
        unset($this->rows);
        $this->toast('success', __('admin.common.deleted'));
    }

    public function bulkDelete(): void
    {
        $this->authorize('deleteAny', $this->modelClass());
        $this->selectedQuery()->get()->each->delete();
        $this->clearSelection();
        unset($this->rows);
        $this->toast('success', __('admin.common.deleted'));
    }

    protected function exportName(): string
    {
        return str_replace(['admin.nav.', '_'], ['', '-'], $this->titleKey());
    }

    protected function exportColumns(): array
    {
        $columns = [];

        foreach ($this->fields() as $field) {
            if (in_array($field['type'], ['asset'], true)) {
                continue;
            }

            $key = $field['key'];

            if (in_array($field['type'], ['pair', 'pair_textarea', 'slug'], true)) {
                $columns[__($field['label']).' (ar)'] = fn (ContentModel $m) => $m->getTranslation($key, 'ar', false);
                $columns[__($field['label']).' (en)'] = fn (ContentModel $m) => $m->getTranslation($key, 'en', false);
            } else {
                $columns[__($field['label'])] = fn (ContentModel $m) => $m->getAttribute($key) instanceof \DateTimeInterface ? $m->getAttribute($key)->format('Y-m-d') : $m->getAttribute($key);
            }
        }

        return $columns;
    }

    protected function authorizeExport(): void
    {
        abort_unless(auth()->user()?->can('export content') || auth()->user()?->hasRole('admin'), 403);
    }

    /**
     * @return class-string<ContentModel>
     */
    public function modelClassName(): string
    {
        return $this->modelClass();
    }

    /**
     * Display value of a table cell (override for computed / HTML cells).
     */
    public function cell(ContentModel $row, string $key): string
    {
        $value = $row->getAttribute($key);

        if (in_array($key, $row->getTranslatableAttributes(), true)) {
            return (string) ($row->getTranslation($key, app()->getLocale(), false) ?: $row->getTranslation($key, 'ar', false));
        }

        return match (true) {
            $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
            $value instanceof \BackedEnum && method_exists($value, 'label') => $value->label(),
            is_bool($value) => $value ? __('admin.common.yes') : __('admin.common.no'),
            is_scalar($value) => (string) $value,
            default => '—',
        };
    }

    public function rowTitle(ContentModel $model): string
    {
        $field = $this->titleField();

        return (string) ($model->getTranslation($field, app()->getLocale(), false) ?: $model->getTranslation($field, 'ar', false));
    }

    public function englishMissing(ContentModel $model): bool
    {
        return blank($model->getTranslation($this->titleField(), 'en', false));
    }

    /**
     * Extra filter chips: list of [key, label, options].
     *
     * @return list<array{0: string, 1: string, 2: array<string, string>}>
     */
    public function filterChips(): array
    {
        return [];
    }

    public function render(): View
    {
        return view('livewire.admin.content.simple-resource')->title(__($this->titleKey()));
    }
}
