<?php

namespace App\Livewire\Admin\Requests;

use App\Enums\ActivityType;
use App\Enums\RequestType;
use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\RequestStatus;
use App\Models\User;
use App\Models\VisitorRequest;
use App\Services\AdminNavigation;
use App\Support\RequestPresenter;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * One list implementation for quote requests, contact messages and job
 * applications: status tabs with counts, search, filters, sorting, bulk
 * status / assign / export / delete and Excel export of the filtered rows.
 *
 * @property-read Collection<int, RequestStatus> $statuses
 * @property-read array<string, int> $statusCounts
 * @property-read array<int, string> $assignees
 */
#[Layout('layouts.admin')]
abstract class RequestIndex extends Component
{
    use WithDataTable;
    use WithToasts;

    public string $bulkStatus = '';

    public string $bulkAssignee = '';

    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    abstract public function type(): RequestType;

    /** Route prefix: quotes / messages / applications. */
    abstract public function routeKey(): string;

    /**
     * Extra filters: key => [label, options].
     *
     * @return array<string, array{0: string, 1: array<int|string, string>}>
     */
    public function extraFilters(): array
    {
        return [];
    }

    /**
     * Type-specific columns for the table: key => [label, callback(row): string].
     *
     * @return array<string, array{0: string, 1: \Closure}>
     */
    public function extraColumns(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', $this->modelClass());
    }

    protected function baseQuery(): Builder
    {
        return ($this->modelClass())::query()->with(['status', 'assignee']);
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = Search::like($term);
        $query->where('name', 'like', $like)
            ->orWhere('phone', 'like', $like)
            ->orWhere('email', 'like', $like);

        if (preg_match('/(\d+)\s*$/', $term, $m)) {
            $query->orWhere($query->getModel()->getQualifiedKeyName(), (int) $m[1]);
        }
    }

    protected function applyFilters(Builder $query): void
    {
        $this->applyCommonFilters($query, ['status']);

        if (filled($this->filters['status'] ?? null)) {
            $query->where('status_id', (int) $this->filters['status']);
        }
    }

    /**
     * Filters shared by every list (the status filter is applied separately so tab counts ignore it).
     *
     * @param  Builder<Model>  $query
     * @param  list<string>  $skip
     */
    protected function applyCommonFilters(Builder $query, array $skip = []): void
    {
        $f = $this->filters;

        if (filled($f['assignee'] ?? null)) {
            $f['assignee'] === 'none' ? $query->whereNull('assigned_to') : $query->where('assigned_to', (int) $f['assignee']);
        }

        if (filled($f['unread'] ?? null)) {
            $query->whereNull('read_at');
        }

        if (filled($f['device'] ?? null)) {
            $query->where('source->device', (string) $f['device']);
        }

        if (filled($f['from'] ?? null) && strtotime((string) $f['from'])) {
            $query->whereDate('created_at', '>=', (string) $f['from']);
        }

        if (filled($f['to'] ?? null) && strtotime((string) $f['to'])) {
            $query->whereDate('created_at', '<=', (string) $f['to']);
        }

        $this->applyTypeFilters($query);
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyTypeFilters(Builder $query): void {}

    protected function sortableColumns(): array
    {
        return ['created' => 'created_at', 'name' => 'name', 'status' => 'status_id'];
    }

    protected function exportName(): string
    {
        return $this->routeKey();
    }

    protected function authorizeExport(): void
    {
        abort_unless(auth()->user()?->can('export requests'), 403);
    }

    protected function exportColumns(): array
    {
        $columns = [
            __('admin.requests.number') => fn (Model $row) => RequestPresenter::number($row),
            __('admin.requests.date') => fn (Model $row) => $row->getAttribute('created_at')?->format('Y-m-d H:i'),
            __('admin.requests.status') => fn (Model $row) => $row->getRelationValue('status')?->getTranslation('name', 'ar'),
            __('admin.requests.assignee') => fn (Model $row) => $row->getRelationValue('assignee')?->name,
        ];

        // Every visitor field, labelled like the e-mails.
        foreach (RequestPresenter::labels(new ($this->modelClass())) as $label) {
            $columns[$label] = fn (Model $row) => RequestPresenter::fields($row, app()->getLocale())[$label] ?? '';
        }

        $columns[__('admin.requests.source')] = fn (Model $row) => collect((array) $row->getAttribute('source'))->map(fn ($v, $k) => $k.': '.(is_array($v) ? implode(', ', $v) : $v))->implode(' | ');

        return $columns;
    }

    #[Computed]
    public function statuses(): Collection
    {
        return RequestStatus::query()->for($this->type())->get();
    }

    /**
     * Rows per status with the other filters applied ('' = all).
     *
     * @return array<string, int>
     */
    #[Computed]
    public function statusCounts(): array
    {
        $eloquent = ($this->modelClass())::query();
        $term = trim($this->search);

        if ($term !== '') {
            $eloquent->where(fn (Builder $q) => $this->applySearch($q, $term));
        }

        $this->applyCommonFilters($eloquent);
        $counts = $eloquent->toBase()->select('status_id', DB::raw('count(*) as aggregate'))->groupBy('status_id')->pluck('aggregate', 'status_id');

        return ['' => (int) $counts->sum()] + $counts->mapWithKeys(fn ($count, $id) => [(string) $id => (int) $count])->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function assignees(): array
    {
        return User::query()->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', ['admin', 'sales']))
            ->orderBy('name')->pluck('name', 'id')->all();
    }

    public function bulkSetStatus(): void
    {
        $this->authorize('update', new ($this->modelClass()));
        $status = $this->statuses->firstWhere('id', (int) $this->bulkStatus);

        if ($status === null) {
            return;
        }

        foreach ($this->selectedQuery()->get() as $row) {
            /** @var VisitorRequest $row */
            if ((int) $row->getAttribute('status_id') !== $status->id) {
                $from = $row->getRelationValue('status')?->getTranslation('name', 'ar');
                $row->forceFill(['status_id' => $status->id])->save();
                $row->logActivity(ActivityType::Status->value, null, ['from' => $from, 'to' => $status->getTranslation('name', 'ar')]);
            }
        }

        $this->bulkStatus = '';
        $this->afterBulk();
    }

    public function bulkAssign(): void
    {
        $this->authorize('update', new ($this->modelClass()));
        $userId = $this->bulkAssignee === 'none' ? null : (int) $this->bulkAssignee;

        if ($userId !== null && ! array_key_exists($userId, $this->assignees)) {
            return;
        }

        foreach ($this->selectedQuery()->get() as $row) {
            /** @var VisitorRequest $row */
            if ($row->getAttribute('assigned_to') !== $userId) {
                $row->forceFill(['assigned_to' => $userId])->save();
                $row->logActivity(ActivityType::Assign->value, null, ['to' => $userId ? $this->assignees[$userId] : null]);
            }
        }

        $this->bulkAssignee = '';
        $this->afterBulk();
    }

    public function bulkMarkRead(): void
    {
        $this->authorize('update', new ($this->modelClass()));
        $this->selectedQuery()->whereNull('read_at')->update(['read_at' => now()]);
        $this->afterBulk();
    }

    public function bulkDelete(): void
    {
        $this->authorize('deleteAny', $this->modelClass());

        foreach ($this->selectedQuery()->get() as $row) {
            $row->delete();
        }

        $this->afterBulk(__('admin.common.deleted'));
    }

    public function delete(int $id): void
    {
        $row = ($this->modelClass())::query()->findOrFail($id);
        $this->authorize('delete', $row);
        $row->delete();
        $this->afterBulk(__('admin.common.deleted'));
    }

    private function afterBulk(?string $message = null): void
    {
        AdminNavigation::forgetCounts();
        $this->clearSelection();
        unset($this->rows, $this->statusCounts);
        $this->toast('success', $message ?? __('admin.common.updated'));
    }

    public function render(): View
    {
        return view('livewire.admin.requests.index')
            ->title(__('admin.nav.'.$this->routeKey()));
    }
}
