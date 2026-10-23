<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Data table behaviour for Livewire list screens:
 * debounced search, filters, sortable columns, per-page, pagination,
 * row selection (+ "select all matching"), bulk actions and Excel export
 * of the current filtered query. All state is URL-synced.
 *
 * Implement:
 *   protected function baseQuery(): Builder                 the unfiltered query (with eager loads)
 *   protected function applySearch(Builder $q, string $t)   search condition
 *   protected function applyFilters(Builder $q): void        optional, reads $this->filters
 *   protected function sortableColumns(): array              ['name' => 'name', 'created' => 'created_at']
 *   protected function defaultSort(): array                  ['created', 'desc']
 *   protected function exportColumns(): array                ['Header' => fn (Model $row) => value]
 *   protected function exportName(): string                  file name without extension
 *
 * View: `$this->rows` (paginator); use <x-admin.table.*> components.
 */
trait WithDataTable
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** @var array<string, mixed> */
    #[Url(as: 'f', except: [])]
    public array $filters = [];

    #[Url(as: 'sort', except: '')]
    public string $sortField = '';

    #[Url(as: 'dir', except: '')]
    public string $sortDirection = '';

    #[Url(as: 'per', except: 25)]
    public int $perPage = 25;

    /** @var list<string> */
    public array $selected = [];

    public bool $selectAllMatching = false;

    abstract protected function baseQuery(): Builder;

    /**
     * @param  Builder<Model>  $query
     */
    protected function applySearch(Builder $query, string $term): void {}

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyFilters(Builder $query): void {}

    /**
     * @return array<string, string>
     */
    protected function sortableColumns(): array
    {
        return ['created' => 'created_at'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function defaultSort(): array
    {
        return ['created', 'desc'];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function exportColumns(): array
    {
        return [];
    }

    protected function exportName(): string
    {
        return 'export';
    }

    public function updatedSearch(): void
    {
        $this->resetTableState();
    }

    public function updatedFilters(): void
    {
        $this->resetTableState();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 25;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, $this->sortableColumns())) {
            return;
        }

        [$currentField, $currentDirection] = $this->currentSort();

        $this->sortDirection = $currentField === $field && $currentDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function setFilter(string $key, mixed $value): void
    {
        if ($value === null || $value === '' || $value === []) {
            unset($this->filters[$key]);
        } else {
            $this->filters[$key] = $value;
        }

        $this->resetTableState();
    }

    public function clearFilters(): void
    {
        $this->filters = [];
        $this->search = '';
        $this->resetTableState();
    }

    public function selectPage(): void
    {
        $this->selected = collect($this->rows()->items())->map(fn (Model $row) => (string) $row->getKey())->all();
        $this->selectAllMatching = false;
    }

    public function selectAll(): void
    {
        $this->selectAllMatching = true;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAllMatching = false;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function currentSort(): array
    {
        [$defaultField, $defaultDirection] = $this->defaultSort();
        $field = array_key_exists($this->sortField, $this->sortableColumns()) ? $this->sortField : $defaultField;
        $direction = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : $defaultDirection;

        return [$field, $direction];
    }

    /**
     * The filtered + sorted query (what the table and the export show).
     *
     * @return Builder<Model>
     */
    public function tableQuery(): Builder
    {
        $query = $this->baseQuery();
        $term = trim($this->search);

        if ($term !== '') {
            $query->where(fn (Builder $q) => $this->applySearch($q, $term));
        }

        $this->applyFilters($query);

        [$field, $direction] = $this->currentSort();
        $column = $this->sortableColumns()[$field] ?? null;

        if ($column !== null) {
            $query->orderBy($column, $direction);
        }

        return $query->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');
    }

    /**
     * Rows targeted by a bulk action.
     *
     * @return Builder<Model>
     */
    protected function selectedQuery(): Builder
    {
        $query = $this->tableQuery();

        return $this->selectAllMatching ? $query : $query->whereKey($this->selected);
    }

    public function selectedCount(): int
    {
        return $this->selectAllMatching ? $this->tableQuery()->count() : count($this->selected);
    }

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return $this->tableQuery()->paginate(in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 25);
    }

    public function export(): BinaryFileResponse
    {
        return $this->writeExport($this->tableQuery());
    }

    public function exportSelected(): BinaryFileResponse
    {
        return $this->writeExport($this->selectedQuery());
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function writeExport(Builder $query): BinaryFileResponse
    {
        $this->authorizeExport();

        $columns = $this->exportColumns();
        $path = storage_path('app/private/exports/'.$this->exportName().'-'.now()->format('Y-m-d-His').'.xlsx');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = SimpleExcelWriter::create($path);
        $writer->addHeader(array_keys($columns));

        foreach ($query->lazy(200) as $row) {
            $values = [];

            foreach ($columns as $callback) {
                $value = $callback($row);
                $values[] = is_scalar($value) || $value === null ? $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $writer->addRow($values);
        }

        $writer->close();

        activity()->causedBy(auth()->user())
            ->withProperties(['file' => basename($path), 'filters' => $this->filters, 'search' => $this->search])
            ->event('exported')
            ->log($this->exportName());

        return response()->download($path)->deleteFileAfterSend();
    }

    /**
     * Override to require a permission for exports.
     */
    protected function authorizeExport(): void {}

    private function resetTableState(): void
    {
        $this->resetPage();
        $this->clearSelection();
        unset($this->rows);
    }
}
