<?php

namespace App\Livewire\Admin\Seo;

use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\Redirect;
use App\Support\LocalizedRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Redirects extends Component
{
    use WithDataTable;
    use WithFileUploads;
    use WithToasts;

    /** @var array{id: int|null, from_path: string, to_path: string, status_code: int} */
    public array $form = ['id' => null, 'from_path' => '', 'to_path' => '', 'status_code' => 301];

    /** @var UploadedFile|null */
    public $csv = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Redirect::class);
    }

    protected function baseQuery(): Builder
    {
        return Redirect::query();
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';
        $query->where('from_path', 'like', $like)->orWhere('to_path', 'like', $like);
    }

    protected function applyFilters(Builder $query): void
    {
        if (($this->filters['auto'] ?? '') !== '') {
            $query->where('is_auto', $this->filters['auto'] === '1');
        }
    }

    protected function sortableColumns(): array
    {
        return ['from' => 'from_path', 'hits' => 'hits', 'last_hit' => 'last_hit_at', 'created' => 'created_at'];
    }

    protected function exportName(): string
    {
        return 'redirects';
    }

    protected function exportColumns(): array
    {
        return [
            'from' => fn (Model $r) => $r->getAttribute('from_path'),
            'to' => fn (Model $r) => $r->getAttribute('to_path'),
            'code' => fn (Model $r) => $r->getAttribute('status_code'),
            'hits' => fn (Model $r) => $r->getAttribute('hits'),
            'last_hit' => fn (Model $r) => $r->getAttribute('last_hit_at')?->format('Y-m-d H:i'),
            'auto' => fn (Model $r) => $r->getAttribute('is_auto') ? 1 : 0,
        ];
    }

    public function edit(?int $id = null): void
    {
        $redirect = $id ? Redirect::query()->findOrFail($id) : null;
        $this->form = [
            'id' => $redirect?->id,
            'from_path' => $redirect->from_path ?? '',
            'to_path' => $redirect->to_path ?? '',
            'status_code' => $redirect->status_code ?? 301,
        ];
        $this->resetErrorBag();
        $this->dispatch('open-modal', name: 'redirect-form');
    }

    public function save(): void
    {
        $this->authorize($this->form['id'] ? 'update' : 'create', new Redirect);

        $this->form['from_path'] = LocalizedRoute::normalizePath(trim($this->form['from_path']));
        $to = trim($this->form['to_path']);
        $this->form['to_path'] = preg_match('#^https?://#i', $to) ? $to : LocalizedRoute::normalizePath($to);

        $this->validate([
            'form.from_path' => ['required', 'string', 'max:500', 'not_in:/', Rule::unique('redirects', 'from_path')->ignore($this->form['id'])],
            'form.to_path' => ['required', 'string', 'max:500', 'different:form.from_path'],
            'form.status_code' => ['required', Rule::in([301, 302])],
        ], [], [
            'form.from_path' => __('admin.redirects.from'),
            'form.to_path' => __('admin.redirects.to'),
        ]);

        if (Redirect::wouldLoop($this->form['from_path'], $this->form['to_path'], $this->form['id'])) {
            $this->addError('form.to_path', __('admin.redirects.loop'));

            return;
        }

        Redirect::query()->updateOrCreate(['id' => $this->form['id']], [
            'from_path' => $this->form['from_path'],
            'to_path' => $this->form['to_path'],
            'status_code' => (int) $this->form['status_code'],
            'is_auto' => false,
        ]);

        unset($this->rows);
        $this->dispatch('close-modal', name: 'redirect-form');
        $this->toast('success', __('admin.common.saved'));
    }

    public function delete(int $id): void
    {
        $redirect = Redirect::query()->findOrFail($id);
        $this->authorize('delete', $redirect);
        $redirect->delete();
        unset($this->rows);
        $this->toast('success', __('admin.common.deleted'));
    }

    public function bulkDelete(): void
    {
        $this->authorize('deleteAny', Redirect::class);
        $this->selectedQuery()->get()->each->delete();
        $this->clearSelection();
        unset($this->rows);
        $this->toast('success', __('admin.common.deleted'));
    }

    /**
     * CSV columns: from, to[, code]. A header row is skipped.
     */
    public function updatedCsv(): void
    {
        $this->authorize('create', new Redirect);
        $this->validate(['csv' => ['required', 'file', 'max:2048', 'mimes:csv,txt']]);

        $handle = fopen($this->csv->getRealPath(), 'r');
        $created = 0;
        $skipped = 0;

        while ($handle && ($row = fgetcsv($handle, escape: '\\')) !== false) {
            $from = trim((string) ($row[0] ?? ''));
            $to = trim((string) ($row[1] ?? ''));

            if ($from === '' || $to === '' || in_array(strtolower($from), ['from', 'from_path'], true)) {
                continue;
            }

            $fromPath = LocalizedRoute::normalizePath($from);
            $toPath = preg_match('#^https?://#i', $to) ? $to : LocalizedRoute::normalizePath($to);
            $code = in_array((int) ($row[2] ?? 301), [301, 302], true) ? (int) ($row[2] ?? 301) : 301;

            if ($fromPath === '/' || $fromPath === $toPath || Redirect::wouldLoop($fromPath, $toPath)) {
                $skipped++;

                continue;
            }

            Redirect::query()->updateOrCreate(['from_path' => $fromPath], ['to_path' => $toPath, 'status_code' => $code, 'is_auto' => false]);
            $created++;
        }

        if ($handle) {
            fclose($handle);
        }

        $this->csv = null;
        unset($this->rows);
        $this->toast($skipped ? 'warning' : 'success', __('admin.redirects.imported', ['count' => $created, 'skipped' => $skipped]));
    }

    public function render(): View
    {
        return view('livewire.admin.seo.redirects')->title(__('admin.nav.redirects'));
    }
}
