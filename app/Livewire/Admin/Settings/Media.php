<?php

namespace App\Livewire\Admin\Settings;

use App\Enums\AssetKind;
use App\Models\Asset;
use App\Services\AssetUploader;
use App\Settings\MediaSettings;

/**
 * Media limits + "regenerate all conversions", processed 20 images per
 * Livewire poll so it never hits shared-hosting time limits.
 */
class Media extends SettingsPage
{
    public bool $regenerating = false;

    public int $regenCursor = 0;

    public int $regenDone = 0;

    public int $regenTotal = 0;

    public function group(): string
    {
        return 'media';
    }

    protected function settingsClass(): string
    {
        return MediaSettings::class;
    }

    protected function rules(): array
    {
        return [
            'data.max_image_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'data.max_video_mb' => ['required', 'integer', 'min:1', 'max:500'],
            'data.max_image_dimension' => ['required', 'integer', 'min:800', 'max:6000'],
            'data.webp_quality' => ['required', 'integer', 'min:40', 'max:100'],
        ];
    }

    protected function prepare(array $data): array
    {
        return array_map('intval', $data);
    }

    public function startRegeneration(): void
    {
        abort_unless(auth()->user()?->can($this->permission()), 403);
        $this->regenTotal = Asset::query()->where('kind', AssetKind::Image->value)->count();
        $this->regenCursor = 0;
        $this->regenDone = 0;
        $this->regenerating = $this->regenTotal > 0;
    }

    public function regenerateChunk(AssetUploader $uploader): void
    {
        if (! $this->regenerating) {
            return;
        }

        $assets = Asset::query()->with('media')
            ->where('kind', AssetKind::Image->value)
            ->where('id', '>', $this->regenCursor)
            ->orderBy('id')
            ->limit(20)
            ->get();

        foreach ($assets as $asset) {
            rescue(fn () => $uploader->regenerateConversions($asset), report: true);
            $this->regenCursor = $asset->id;
            $this->regenDone++;
        }

        if ($assets->count() < 20) {
            $this->regenerating = false;
            $this->toast('success', __('admin.settings.regenerated', ['count' => $this->regenDone]));
        }
    }
}
