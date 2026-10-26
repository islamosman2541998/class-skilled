<?php

namespace App\Livewire\Admin;

use App\Models\Asset;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Form control for library assets, bound with wire:model:
 *   <livewire:admin.asset-field wire:model="form.cover_id" kind="image" :label="…" :error="$errors->first('form.cover_id')" />
 *   <livewire:admin.asset-field wire:model="form.gallery_ids" multiple kind="image" :label="…" />
 * Single mode holds an id (or null); multiple mode an ordered list of ids
 * (drag to reorder; the picker adds to it, × removes). Opens the global AssetPicker.
 */
class AssetField extends Component
{
    /** @var int|list<int>|null */
    #[Modelable]
    public int|array|null $value = null;

    public bool $multiple = false;

    public ?string $kind = null;

    public ?string $label = null;

    public ?string $hint = null;

    public bool $required = false;

    #[Reactive]
    public ?string $error = null;

    public string $fieldId = '';

    public function mount(): void
    {
        $this->fieldId = 'af-'.Str::random(8);
    }

    /**
     * @param  list<int>  $ids
     */
    #[On('asset-picked')]
    public function picked(string $target, array $ids): void
    {
        if ($target !== $this->fieldId) {
            return;
        }

        $ids = array_map('intval', $ids);
        // Multiple mode: the picker opens empty and its choice is added after the current items.
        $this->value = $this->multiple
            ? array_values(array_unique([...$this->ids(), ...$ids]))
            : ($ids[0] ?? null);
        $this->changed();
    }

    public function remove(int $id): void
    {
        $this->value = $this->multiple
            ? array_values(array_diff($this->ids(), [$id]))
            : null;
        $this->changed();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        $this->value = array_values(array_intersect(array_map('intval', $ids), $this->ids()));
        $this->changed();
    }

    /**
     * Forget the cached thumbnails and tell the page (e.g. a live preview) the value changed.
     */
    private function changed(): void
    {
        unset($this->assets);
        $this->dispatch('asset-field-changed', field: $this->fieldId);
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return array_values(array_filter(array_map('intval', (array) $this->value)));
    }

    /**
     * @return Collection<int, Asset>
     */
    #[Computed]
    public function assets(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return new Collection;
        }

        $assets = Asset::query()->with(['media', 'poster.media'])->whereIn('id', $ids)->get()->keyBy('id');

        return new Collection(array_values(array_filter(array_map(fn (int $id) => $assets->get($id), $ids))));
    }

    public function render(): View
    {
        return view('livewire.admin.asset-field');
    }
}
