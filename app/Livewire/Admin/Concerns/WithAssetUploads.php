<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Asset;
use App\Services\AssetUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * Server side of the Alpine `assetUploader` (resources/js/admin/uploader.js):
 * the browser uploads to $upload / $posterUpload, then calls storeUpload().
 * Components implement `uploadFolderId()` and may override `afterUpload()`.
 */
trait WithAssetUploads
{
    use WithFileUploads;

    /** @var UploadedFile|null */
    public $upload = null;

    /** @var UploadedFile|null */
    public $posterUpload = null;

    protected function uploadFolderId(): ?int
    {
        return null;
    }

    protected function afterUpload(Asset $asset): void {}

    /**
     * @param  array<string, mixed>  $meta  kind, duration, poster_asset_id
     */
    public function storeUpload(string $property, array $meta = []): ?int
    {
        $this->authorize('create', Asset::class);

        $poster = $property === 'posterUpload';
        $file = $poster ? $this->posterUpload : $this->upload;

        if (! $file instanceof UploadedFile) {
            return null;
        }

        try {
            $attributes = ['folder_id' => $this->uploadFolderId()];

            if (($meta['kind'] ?? null) === 'video') {
                $attributes['duration'] = isset($meta['duration']) ? (int) $meta['duration'] : null;
                $attributes['poster_asset_id'] = isset($meta['poster_asset_id']) ? (int) $meta['poster_asset_id'] : null;
            }

            $asset = app(AssetUploader::class)->upload($file, $attributes);
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: $file->getClientOriginalName().': '.collect($e->errors())->flatten()->first());

            return null;
        } finally {
            $this->reset($poster ? 'posterUpload' : 'upload');
        }

        if (! $poster) {
            $this->afterUpload($asset);
        }

        return $asset->id;
    }
}
