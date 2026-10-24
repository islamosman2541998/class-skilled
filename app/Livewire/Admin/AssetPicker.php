<?php

namespace App\Livewire\Admin;

use App\Enums\AssetKind;
use App\Livewire\Admin\Concerns\WithAssetUploads;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\Asset;
use App\Models\MediaFolder;
use App\Settings\MediaSettings;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Global media picker modal (one instance in the admin layout).
 * Open: $dispatch('open-asset-picker', {target, multiple, kind, selected}); kind = one kind or a comma list.
 * On confirm it dispatches `asset-picked` {target, ids} back to the caller
 * (see App\Livewire\Admin\AssetField). Includes the uploader (drag & drop,
 * per-file progress, video poster capture).
 */
class AssetPicker extends Component
{
    use WithAssetUploads;
    use WithToasts;

    public bool $open = false;

    public string $target = '';

    public bool $multiple = false;

    public ?string $kind = null;

    /** @var list<int> */
    public array $selected = [];

    public string $tab = 'library';

    public string $search = '';

    public ?int $folder = null;

    public bool $missingAlt = false;

    public int $limit = 24;

    /**
     * @param  list<int>|int|null  $selected
     */
    #[On('open-asset-picker')]
    public function openPicker(string $target, bool $multiple = false, ?string $kind = null, array|int|null $selected = []): void
    {
        $this->authorize('viewAny', Asset::class);

        $this->target = $target;
        $this->multiple = $multiple;
        $kinds = array_values(array_intersect(explode(',', (string) $kind), AssetKind::values()));
        $this->kind = $kinds === [] ? null : implode(',', $kinds);
        $this->selected = array_values(array_filter(array_map('intval', (array) $selected)));
        $this->tab = 'library';
        $this->search = '';
        $this->limit = 24;
        $this->open = true;
        unset($this->assets);
    }

    /**
     * Allowed kinds (`kind` may be one kind or a comma list, e.g. "image,video,embed"); empty = all.
     *
     * @return list<string>
     */
    public function kinds(): array
    {
        return $this->kind === null ? [] : explode(',', $this->kind);
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function updatedSearch(): void
    {
        $this->limit = 24;
    }

    public function loadMore(): void
    {
        $this->limit += 24;
    }

    public function toggle(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));

            return;
        }

        $this->selected = $this->multiple ? [...$this->selected, $id] : [$id];
    }

    public function confirm(): void
    {
        $this->dispatch('asset-picked', target: $this->target, ids: $this->selected);
        $this->open = false;
    }

    protected function uploadFolderId(): ?int
    {
        return $this->folder;
    }

    protected function afterUpload(Asset $asset): void
    {
        if ($this->kinds() === [] || in_array($asset->kind->value, $this->kinds(), true)) {
            $this->selected = $this->multiple ? [...$this->selected, $asset->id] : [$asset->id];
        }

        unset($this->assets);
    }

    public function uploadsFinished(): void
    {
        $this->tab = 'library';
        $this->toast('success', __('admin.media.uploaded'));
    }

    /**
     * @return Collection<int, Asset>
     */
    #[Computed]
    public function assets(): Collection
    {
        if (! $this->open) {
            return new Collection;
        }

        return Asset::query()
            ->with(['media', 'poster.media'])
            ->when($this->kinds() !== [], fn ($q) => $q->whereIn('kind', $this->kinds()))
            ->when($this->folder, fn ($q) => $q->where('folder_id', $this->folder))
            ->when($this->missingAlt, fn ($q) => $q->missingAlt(app()->getLocale()))
            ->when(trim($this->search) !== '', fn ($q) => Search::apply($q, $this->search, ['original_name'], ['alt', 'title']))
            ->latest('id')
            ->limit($this->limit + 1)
            ->get();
    }

    /**
     * @return Collection<int, MediaFolder>
     */
    #[Computed]
    public function folders(): Collection
    {
        return MediaFolder::query()->orderBy('sort')->get();
    }

    public function render(): View
    {
        return view('livewire.admin.asset-picker', [
            'media' => app(MediaSettings::class),
        ]);
    }
}
