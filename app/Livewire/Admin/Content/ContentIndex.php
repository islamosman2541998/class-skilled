<?php

namespace App\Livewire\Admin\Content;

use App\Enums\PublishStatus;
use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Sortable;
use App\Models\ContentModel;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Base for content lists: search over translatable fields, status tabs
 * (published / draft / scheduled), "missing English" filter, inline toggles,
 * bulk publish / unpublish / feature / delete, drag reorder and Excel export.
 */
#[Layout('layouts.admin')]
abstract class ContentIndex extends Component
{
    use WithDataTable;
    use WithToasts;

    /**
     * @return class-string<ContentModel>
     */
    abstract protected function modelClass(): string;

    abstract protected function view(): string;

    abstract protected function titleKey(): string;

    /**
     * @return list<string>
     */
    protected function translatableSearch(): array
    {
        return ['title'];
    }

    protected function titleField(): string
    {
        return 'title';
    }

    protected function publishable(): bool
    {
        return in_array(Publishable::class, class_uses_recursive($this->modelClass()), true);
    }

    protected function sortable(): bool
    {
        return in_array(Sortable::class, class_uses_recursive($this->modelClass()), true);
    }

    /**
     * @return list<string>
     */
    protected function eagerLoads(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', $this->modelClass());
    }

    protected function baseQuery(): Builder
    {
        return ($this->modelClass())::query()->with($this->eagerLoads());
    }

    protected function applySearch(Builder $query, string $term): void
    {
        Search::apply($query, $term, [], $this->translatableSearch());
    }

    protected function applyFilters(Builder $query): void
    {
        $status = $this->filters['status'] ?? '';

        if ($this->publishable() && $status !== '') {
            $this->whereStatus($query, (string) $status);
        }

        if (($this->filters['missing'] ?? '') === '1') {
            $field = $this->titleField();
            $query->where(fn (Builder $q) => $q->whereNull($field.'->en')->orWhere($field.'->en', ''));
        }

        if (($this->filters['featured'] ?? '') === '1') {
            $query->where('is_featured', true);
        }

        $this->applyModuleFilters($query);
    }

    protected function applyModuleFilters(Builder $query): void {}

    protected function sortableColumns(): array
    {
        return array_filter([
            'sort' => $this->sortable() ? 'sort' : null,
            'updated' => 'updated_at',
            'created' => 'created_at',
            'published' => $this->publishable() ? 'published_at' : null,
        ]);
    }

    protected function defaultSort(): array
    {
        return $this->sortable() ? ['sort', 'asc'] : ['created', 'desc'];
    }

    /**
     * Drag reorder is offered only when the list shows everything in manual order.
     */
    public function canReorder(): bool
    {
        return $this->sortable() && $this->currentSort() === ['sort', 'asc'] && $this->search === '' && $this->filters === [];
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

    /**
     * @return array<string, array{0: string, 1: int|null}>
     */
    public function statusTabs(): array
    {
        if (! $this->publishable()) {
            return [];
        }

        $class = $this->modelClass();
        $count = fn (string $status) => $this->whereStatus($class::query(), $status)->count();

        return [
            '' => [__('admin.common.all'), $class::query()->count()],
            'published' => [__('enums.publish_status.published'), $count('published')],
            'draft' => [__('enums.publish_status.draft'), $count('draft')],
            'scheduled' => [__('enums.publish_status.scheduled'), $count('scheduled')],
        ];
    }

    /**
     * Same conditions as the Publishable scopes, usable on a generic query.
     *
     * @template TModel of ContentModel
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function whereStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'published' => $query->where('status', PublishStatus::Published->value)->whereNotNull('published_at')->where('published_at', '<=', now()),
            'scheduled' => $query->where('status', PublishStatus::Published->value)->where('published_at', '>', now()),
            'draft' => $query->where('status', PublishStatus::Draft->value),
            default => $query,
        };
    }

    /**
     * Inline switch for is_featured / is_active / show_on_site / published.
     */
    public function toggle(int $id, string $field): void
    {
        $model = ($this->modelClass())::query()->findOrFail($id);
        $this->authorize('update', $model);

        if ($field === 'published' && $this->publishable()) {
            $published = $model->getAttribute('status') === PublishStatus::Published;
            $model->setAttribute('status', $published ? PublishStatus::Draft : PublishStatus::Published);

            if (! $published && $model->getAttribute('published_at') === null) {
                $model->setAttribute('published_at', now());
            }
        } elseif (in_array($field, ['is_featured', 'is_active', 'show_on_site', 'is_visible'], true)) {
            $model->setAttribute($field, ! $model->getAttribute($field));
        } else {
            return;
        }

        $model->save();
        unset($this->rows);
        $this->toast('success', __('admin.common.updated'));
    }

    public function bulk(string $action): void
    {
        $class = $this->modelClass();
        $this->authorize($action === 'delete' ? 'deleteAny' : 'update', $action === 'delete' ? $class : new $class);
        $count = 0;

        foreach ($this->selectedQuery()->get() as $model) {
            match ($action) {
                'publish' => $model->forceFill(['status' => PublishStatus::Published, 'published_at' => $model->getAttribute('published_at') ?? now()])->save(),
                'unpublish' => $model->forceFill(['status' => PublishStatus::Draft])->save(),
                'feature' => $model->forceFill(['is_featured' => true])->save(),
                'unfeature' => $model->forceFill(['is_featured' => false])->save(),
                'activate' => $model->forceFill(['is_active' => true])->save(),
                'deactivate' => $model->forceFill(['is_active' => false])->save(),
                'delete' => $model->delete(),
                default => null,
            };
            $count++;
        }

        $this->clearSelection();
        unset($this->rows);
        $this->toast('success', $action === 'delete' ? __('admin.common.deleted') : __('admin.content.bulk_done', ['count' => $count]));
    }

    public function delete(int $id): void
    {
        $model = ($this->modelClass())::query()->findOrFail($id);
        $this->authorize('delete', $model);
        $model->delete();
        unset($this->rows);
        $this->toast('success', __('admin.common.deleted'));
    }

    protected function authorizeExport(): void
    {
        abort_unless(auth()->user()?->can('export content') || auth()->user()?->hasRole('admin'), 403);
    }

    public function englishMissing(ContentModel $model): bool
    {
        $field = $this->titleField();

        return blank($model->getTranslation($field, 'en', false));
    }

    protected function exportName(): string
    {
        return str_replace(['admin.nav.', '_'], ['', '-'], $this->titleKey());
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    public function render(): View
    {
        return view($this->view(), $this->viewData())->title(__($this->titleKey()));
    }
}
