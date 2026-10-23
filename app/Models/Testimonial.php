<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: photo.
 *
 * @property int $id
 * @property array<string, string> $name
 * @property array<string, string>|null $company
 * @property array<string, string> $body
 * @property int|null $rating
 * @property int|null $project_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project|null $project
 */
class Testimonial extends Model
{
    use ClearsSiteCache, HasAssets, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['name', 'company', 'body', 'rating', 'project_id', 'is_active', 'sort'];

    /** @var list<string> */
    public array $translatable = ['name', 'company', 'body'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'rating' => 'integer'];
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
