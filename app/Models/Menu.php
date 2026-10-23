<?php

namespace App\Models;

use App\Enums\MenuLocation;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property MenuLocation $location
 * @property array<string, string> $name
 * @property bool $use_header_menu
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, MenuItem> $items
 * @property-read Collection<int, MenuItem> $rootItems
 */
class Menu extends Model
{
    use ClearsSiteCache, HasTranslations, LogsModelActivity;

    protected $fillable = ['location', 'name', 'use_header_menu', 'is_active'];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'location' => MenuLocation::class,
            'use_header_menu' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort');
    }
}
