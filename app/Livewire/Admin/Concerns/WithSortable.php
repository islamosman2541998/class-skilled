<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Drag & drop ordering for list screens:
 *   <tbody x-sortable.handle="reorder"> <tr data-sort-id="{{ $row->id }}"> <td><button data-sort-handle>…
 * The model must use App\Models\Concerns\Sortable. Requires "update {module}".
 */
trait WithSortable
{
    /**
     * @return class-string<Model>
     */
    abstract protected function sortableModel(): string;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        $model = $this->sortableModel();
        $this->authorize('update', new $model);
        $model::reorder($ids);
        unset($this->rows);
        $this->dispatch('toast', type: 'success', message: __('admin.common.reordered'));
    }
}
