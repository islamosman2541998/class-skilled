<?php

namespace App\Models;

use App\Enums\DataSource;
use App\Enums\SectionBackground;
use App\Enums\SectionType;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * One block of the home page. Type-specific settings live in `data`
 * (asset ids included); referenced assets are mirrored into the `data`
 * asset collection for usage tracking. The background photo of dark_photo
 * sections is `data.bg_asset_id`.
 *
 * @property int $id
 * @property SectionType $type
 * @property array<string, string>|null $title
 * @property array<string, string>|null $subtitle
 * @property array<string, string>|null $cta_label
 * @property array<string, mixed>|null $cta_link
 * @property array<string, mixed>|null $data
 * @property DataSource $source
 * @property int|null $items_limit
 * @property SectionBackground $background
 * @property bool $is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class HomeSection extends ContentModel
{
    use ClearsSiteCache, HasAssets, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = [
        'type', 'title', 'subtitle', 'cta_label', 'cta_link', 'data', 'source', 'items_limit',
        'background', 'is_visible', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'subtitle', 'cta_label'];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'cta_link' => 'array',
            'data' => 'array',
            'source' => DataSource::class,
            'background' => SectionBackground::class,
            'is_visible' => 'boolean',
            'items_limit' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (HomeSection $section): void {
            if ($section->wasRecentlyCreated || $section->wasChanged('data')) {
                $section->syncAssets('data', $section->referencedAssetIds(), touch: false);
            }
        });
    }

    /**
     * @param  Builder<HomeSection>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * Every `*asset_id` / `*asset_ids` value found anywhere inside `data`.
     *
     * @return list<int>
     */
    public function referencedAssetIds(): array
    {
        $ids = [];

        foreach (Arr::dot($this->data ?? []) as $key => $value) {
            $last = (string) last(explode('.', (string) $key));
            $parent = explode('.', (string) $key);
            array_pop($parent);

            if ((str_ends_with($last, 'asset_id') || str_ends_with((string) last($parent), 'asset_ids')) && is_numeric($value)) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Manual picks for list sections (`data.item_ids`), in order.
     *
     * @return list<int>
     */
    public function manualIds(): array
    {
        return array_values(array_map('intval', (array) ($this->data['item_ids'] ?? [])));
    }
}
