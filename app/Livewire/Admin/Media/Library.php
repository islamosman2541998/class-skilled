<?php

namespace App\Livewire\Admin\Media;

use App\Enums\AssetKind;
use App\Livewire\Admin\Concerns\WithAssetUploads;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\Asset;
use App\Models\MediaFolder;
use App\Services\AssetUploader;
use App\Support\AdminLinks;
use App\Support\Search;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Media library: folders · grid/list of assets · details panel.
 */
#[Layout('layouts.admin')]
class Library extends Component
{
    use WithAssetUploads;
    use WithToasts;

    #[Url(as: 'folder', except: '')]
    public string $folder = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'kind', except: '')]
    public string $kind = '';

    #[Url(as: 'alt', except: false)]
    public bool $missingAlt = false;

    #[Url(as: 'sort', except: 'newest')]
    public string $sort = 'newest';

    #[Url(as: 'view', except: 'grid')]
    public string $view = 'grid';

    public int $limit = 30;

    /** @var list<int> */
    public array $selected = [];

    public ?int $active = null;

    public bool $showUploader = false;

    /** @var array<string, mixed> */
    public array $details = [];

    /** @var UploadedFile|null */
    public $replacement = null;

    /** @var array{id: int|null, name: array{ar: string, en: string}, parent_id: int|string|null} */
    public array $folderForm = ['id' => null, 'name' => ['ar' => '', 'en' => ''], 'parent_id' => null];

    /** @var array{url: string, poster: int|null, title: array{ar: string, en: string}} */
    public array $embed = ['url' => '', 'poster' => null, 'title' => ['ar' => '', 'en' => '']];

    public string $moveTo = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Asset::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['folder', 'search', 'kind', 'missingAlt', 'sort'], true)) {
            $this->limit = 30;
            $this->selected = [];
            unset($this->assets);
        }

        if ($property === 'replacement' && $this->replacement instanceof UploadedFile) {
            $this->replaceFile();
        }
    }

    public function loadMore(): void
    {
        $this->limit += 30;
    }

    protected function uploadFolderId(): ?int
    {
        return is_numeric($this->folder) ? (int) $this->folder : null;
    }

    protected function afterUpload(Asset $asset): void
    {
        unset($this->assets, $this->stats, $this->folders);
    }

    public function uploadsFinished(): void
    {
        $this->toast('success', __('admin.media.uploaded'));
    }

    // ----- Query -----

    /**
     * @return Builder<Asset>
     */
    private function query(): Builder
    {
        $query = Asset::query();

        if ($this->folder === 'none') {
            $query->whereNull('folder_id');
        } elseif (is_numeric($this->folder)) {
            $query->where('folder_id', (int) $this->folder);
        }

        if (in_array($this->kind, AssetKind::values(), true)) {
            $query->where('kind', $this->kind);
        }

        if ($this->missingAlt) {
            $query->where(fn (Builder $q) => $q->missingAlt('ar')->orWhere(fn (Builder $q2) => $q2->missingAlt('en')));
        }

        if (trim($this->search) !== '') {
            Search::apply($query, $this->search, ['original_name'], ['alt', 'title']);
        }

        return match ($this->sort) {
            'name' => $query->orderBy('original_name'),
            'size' => $query->orderByDesc('size'),
            'oldest' => $query->orderBy('id'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * @return Collection<int, Asset>
     */
    #[Computed]
    public function assets(): Collection
    {
        return $this->query()->with(['media', 'poster.media'])->limit($this->limit + 1)->get();
    }

    #[Computed]
    public function total(): int
    {
        return $this->query()->count();
    }

    /**
     * @return array{count: int, size: int}
     */
    #[Computed]
    public function stats(): array
    {
        return ['count' => Asset::query()->count(), 'size' => (int) Asset::query()->sum('size')];
    }

    /**
     * @return Collection<int, MediaFolder>
     */
    #[Computed]
    public function folders(): Collection
    {
        return MediaFolder::query()->withCount('assets')->orderBy('sort')->orderBy('id')->get();
    }

    #[Computed]
    public function activeAsset(): ?Asset
    {
        return $this->active ? Asset::query()->with(['media', 'poster.media', 'folder', 'uploader'])->find($this->active) : null;
    }

    // ----- Selection & details -----

    public function open(int $id): void
    {
        $asset = Asset::query()->findOrFail($id);
        $this->active = $asset->id;
        $this->details = [
            'alt' => Translate::pair($asset->alt),
            'title' => Translate::pair($asset->title),
            'caption' => Translate::pair($asset->caption),
            'focal' => $asset->focal,
            'folder_id' => $asset->folder_id,
        ];
        $this->resetErrorBag();
        unset($this->activeAsset);
    }

    public function closeDetails(): void
    {
        $this->active = null;
        $this->details = [];
    }

    public function toggleSelect(int $id): void
    {
        $this->selected = in_array($id, $this->selected, true)
            ? array_values(array_diff($this->selected, [$id]))
            : [...$this->selected, $id];
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function saveDetails(AssetUploader $uploader): void
    {
        $asset = Asset::query()->findOrFail((int) $this->active);
        $this->authorize('update', $asset);

        $this->validate([
            'details.alt.ar' => [$asset->isImage() ? 'required' : 'nullable', 'string', 'max:250'],
            'details.alt.en' => ['nullable', 'string', 'max:250'],
            'details.title.ar' => ['nullable', 'string', 'max:250'],
            'details.title.en' => ['nullable', 'string', 'max:250'],
            'details.caption.ar' => ['nullable', 'string', 'max:500'],
            'details.caption.en' => ['nullable', 'string', 'max:500'],
            'details.focal' => ['required', 'in:top,center,bottom'],
            'details.folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ], [], ['details.alt.ar' => __('admin.media_library.alt')]);

        $focalChanged = $asset->focal !== $this->details['focal'];

        $asset->update([
            'alt' => array_filter(Translate::pair($this->details['alt'])),
            'title' => array_filter(Translate::pair($this->details['title'])),
            'caption' => array_filter(Translate::pair($this->details['caption'])),
            'focal' => $this->details['focal'],
            'folder_id' => $this->details['folder_id'] ?: null,
        ]);

        if ($focalChanged && $asset->isImage()) {
            $uploader->regenerateConversions($asset->fresh() ?? $asset);
        }

        unset($this->assets, $this->activeAsset, $this->folders);
        $this->toast('success', __('admin.common.saved'));
    }

    private function replaceFile(): void
    {
        $asset = Asset::query()->findOrFail((int) $this->active);
        $this->authorize('update', $asset);

        try {
            app(AssetUploader::class)->replace($asset, $this->replacement);
            $this->toast('success', __('admin.media_library.replaced'));
        } catch (ValidationException $e) {
            $this->toast('error', (string) collect($e->errors())->flatten()->first());
        } finally {
            $this->replacement = null;
        }

        unset($this->assets, $this->activeAsset, $this->stats);
    }

    public function delete(int $id, bool $force = false): void
    {
        $asset = Asset::query()->findOrFail($id);
        $this->authorize('delete', $asset);

        if ($asset->isUsed() && ! ($force && auth()->user()?->hasRole('admin'))) {
            $this->toast('error', __('admin.media_library.in_use'));

            return;
        }

        $this->forceDeleteAsset($asset);

        if ($this->active === $id) {
            $this->closeDetails();
        }

        $this->selected = array_values(array_diff($this->selected, [$id]));
        unset($this->assets, $this->stats, $this->folders);
        $this->toast('success', __('admin.common.deleted'));
    }

    private function forceDeleteAsset(Asset $asset): void
    {
        DB::table('assetables')->where('asset_id', $asset->id)->delete();
        $asset->clearMediaCollection('file');
        $asset->forceDelete();
    }

    public function bulkDelete(): void
    {
        $this->authorize('deleteAny', Asset::class);
        $skipped = 0;

        foreach (Asset::query()->whereKey($this->selected)->get() as $asset) {
            if ($asset->isUsed()) {
                $skipped++;

                continue;
            }

            $this->forceDeleteAsset($asset);
        }

        $this->selected = [];
        unset($this->assets, $this->stats, $this->folders);
        $this->toast($skipped ? 'warning' : 'success', $skipped ? __('admin.media_library.some_in_use', ['count' => $skipped]) : __('admin.common.deleted'));
    }

    public function bulkMove(): void
    {
        $this->authorize('update', new Asset);
        $target = $this->moveTo === 'none' || $this->moveTo === '' ? null : (int) $this->moveTo;

        if ($target !== null && ! MediaFolder::query()->whereKey($target)->exists()) {
            return;
        }

        foreach (Asset::query()->whereKey($this->selected)->get() as $asset) {
            $asset->update(['folder_id' => $target]);
        }

        $this->selected = [];
        $this->moveTo = '';
        unset($this->assets, $this->folders);
        $this->dispatch('close-modal', name: 'move-assets');
        $this->toast('success', __('admin.common.updated'));
    }

    // ----- Folders -----

    public function editFolder(?int $id = null): void
    {
        $folder = $id ? MediaFolder::query()->findOrFail($id) : null;
        $this->folderForm = [
            'id' => $folder?->id,
            'name' => Translate::pair($folder?->name),
            'parent_id' => $folder?->parent_id,
        ];
        $this->resetErrorBag();
        $this->dispatch('open-modal', name: 'folder-form');
    }

    public function saveFolder(): void
    {
        $this->authorize('create', new MediaFolder);
        $this->validate([
            'folderForm.name.ar' => ['required', 'string', 'max:80'],
            'folderForm.name.en' => ['nullable', 'string', 'max:80'],
            'folderForm.parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ], [], ['folderForm.name.ar' => __('admin.media_library.folder_name')]);

        $parent = $this->folderForm['parent_id'] ?: null;

        if ($parent !== null && (int) $parent === (int) $this->folderForm['id']) {
            $parent = null;
        }

        MediaFolder::query()->updateOrCreate(
            ['id' => $this->folderForm['id']],
            ['name' => array_filter(Translate::pair($this->folderForm['name'])), 'parent_id' => $parent],
        );

        unset($this->folders);
        $this->dispatch('close-modal', name: 'folder-form');
        $this->toast('success', __('admin.common.saved'));
    }

    public function deleteFolder(int $id): void
    {
        $folder = MediaFolder::query()->withCount(['assets', 'children'])->findOrFail($id);
        $this->authorize('delete', $folder);

        if ($folder->assets_count > 0 || $folder->children_count > 0) {
            $this->toast('error', __('admin.media_library.folder_not_empty'));

            return;
        }

        $folder->delete();

        if ($this->folder === (string) $id) {
            $this->folder = '';
        }

        unset($this->folders);
        $this->toast('success', __('admin.common.deleted'));
    }

    // ----- Embeds -----

    public function saveEmbed(AssetUploader $uploader): void
    {
        $this->authorize('create', Asset::class);
        $this->validate([
            'embed.url' => ['required', 'url', 'max:500'],
            'embed.poster' => ['required', 'integer', 'exists:assets,id'],
            'embed.title.ar' => ['required', 'string', 'max:200'],
            'embed.title.en' => ['nullable', 'string', 'max:200'],
        ], [], [
            'embed.url' => __('admin.media_library.embed_url'),
            'embed.poster' => __('admin.media_library.poster'),
            'embed.title.ar' => __('admin.media_library.title'),
        ]);

        try {
            $uploader->createEmbed($this->embed['url'], (int) $this->embed['poster'], [
                'title' => array_filter(Translate::pair($this->embed['title'])),
                'folder_id' => $this->uploadFolderId(),
            ]);
        } catch (ValidationException $e) {
            $this->addError('embed.url', (string) collect($e->errors())->flatten()->first());

            return;
        }

        $this->embed = ['url' => '', 'poster' => null, 'title' => ['ar' => '', 'en' => '']];
        unset($this->assets, $this->stats);
        $this->dispatch('close-modal', name: 'embed-form');
        $this->toast('success', __('admin.common.created'));
    }

    /**
     * @return list<array{label: string, title: string, url: string|null}>
     */
    public function usageList(Asset $asset): array
    {
        $list = [];

        foreach ($asset->usages() as $usage) {
            if ($usage->assetable_type === 'settings') {
                $list[] = ['label' => __('admin.nav.settings'), 'title' => __('admin.media_library.settings_refs.'.$usage->collection), 'url' => AdminLinks::editUrl('settings')];

                continue;
            }

            $model = AdminLinks::model($usage->assetable_type, (int) $usage->assetable_id);
            $list[] = [
                'label' => AdminLinks::typeLabel($usage->assetable_type),
                'title' => AdminLinks::title($model),
                'url' => AdminLinks::editUrl($usage->assetable_type, $usage->assetable_id),
            ];
        }

        return $list;
    }

    public function missingEnglishDetails(): bool
    {
        return ! Translate::complete([$this->details['alt'] ?? [], $this->details['title'] ?? [], $this->details['caption'] ?? []]);
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format(max(1, $bytes / 1024)).' KB';
    }

    public function render(): View
    {
        return view('livewire.admin.media.library')->title(__('admin.nav.library'));
    }
}
