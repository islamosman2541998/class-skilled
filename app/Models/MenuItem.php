<?php

namespace App\Models;

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\Visibility;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Menu entry (max depth 2). Empty label = inherit the target's title.
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property MenuItemType $type
 * @property array<string, string>|null $label
 * @property string|null $linkable_type
 * @property int|null $linkable_id
 * @property string|null $system_route
 * @property string|null $url
 * @property DynamicSource|null $dynamic_source
 * @property DataSource|null $dynamic_mode
 * @property int|null $dynamic_limit
 * @property bool $show_all_link
 * @property bool $show_descriptions
 * @property bool $target_blank
 * @property Visibility $visibility
 * @property bool $is_active
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Menu $menu
 * @property-read MenuItem|null $parent
 * @property-read Collection<int, MenuItem> $children
 * @property-read Collection<int, MenuItemEntry> $entries
 * @property-read Model|null $linkable
 */
class MenuItem extends Model
{
    use ClearsSiteCache, HasTranslations, LogsModelActivity;

    protected $fillable = [
        'menu_id', 'parent_id', 'type', 'label', 'linkable_type', 'linkable_id', 'system_route', 'url',
        'dynamic_source', 'dynamic_mode', 'dynamic_limit', 'show_all_link', 'show_descriptions',
        'target_blank', 'visibility', 'is_active', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['label'];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'dynamic_source' => DynamicSource::class,
            'dynamic_mode' => DataSource::class,
            'dynamic_limit' => 'integer',
            'show_all_link' => 'boolean',
            'show_descriptions' => 'boolean',
            'target_blank' => 'boolean',
            'visibility' => Visibility::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Manual picks of a dynamic group.
     *
     * @return HasMany<MenuItemEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(MenuItemEntry::class)->orderBy('sort');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
