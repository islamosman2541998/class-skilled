<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array<string, string> $name
 * @property array<string, string> $slug
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Project> $projects
 */
class ProjectCategory extends Model
{
    use ClearsSiteCache, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = ['name', 'slug', 'sort', 'is_active'];

    /** @var list<string> */
    public array $translatable = ['name', 'slug'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function publicRouteName(): string
    {
        return 'projects.category';
    }

    /**
     * @param  Builder<ProjectCategory>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'category_id');
    }

    protected function seoDescriptionFields(): array
    {
        return [];
    }
}
