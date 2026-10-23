<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: photo.
 *
 * @property int $id
 * @property array<string, string> $name
 * @property array<string, string>|null $role
 * @property array<string, string>|null $bio
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TeamMember extends Model
{
    use ClearsSiteCache, HasAssets, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['name', 'role', 'bio', 'is_active', 'sort'];

    /** @var list<string> */
    public array $translatable = ['name', 'role', 'bio'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @param  Builder<TeamMember>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
