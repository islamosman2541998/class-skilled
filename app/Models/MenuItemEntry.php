<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $menu_item_id
 * @property string $entry_type
 * @property int $entry_id
 * @property int $sort
 * @property-read MenuItem $menuItem
 * @property-read Model|null $entry
 */
class MenuItemEntry extends Model
{
    use ClearsSiteCache;

    public $timestamps = false;

    protected $fillable = ['menu_item_id', 'entry_type', 'entry_id', 'sort'];

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function entry(): MorphTo
    {
        return $this->morphTo();
    }
}
