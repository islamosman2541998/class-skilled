<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\HasBlocks;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Sortable;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: cover, gallery, blocks.
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $excerpt
 * @property array<string, string>|null $summary
 * @property array<string, list<string>>|null $scope
 * @property array<string, list<string>>|null $stages
 * @property bool $is_featured
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Project> $projects
 * @property-read Collection<int, Faq> $faqs
 */
class Service extends ContentModel
{
    use ClearsSiteCache, HasAssets, HasBlocks, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Sortable;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'summary', 'blocks', 'scope', 'stages', 'is_featured', 'is_active', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'excerpt', 'summary'];

    protected function casts(): array
    {
        return [
            'scope' => 'array',
            'stages' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function publicRouteName(): string
    {
        return 'services.show';
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }

    /**
     * @return list<string>
     */
    public function workScope(?string $locale = null): array
    {
        return Translate::list($this->scope, $locale);
    }

    /**
     * @return list<string>
     */
    public function stagesList(?string $locale = null): array
    {
        return Translate::list($this->stages, $locale);
    }

    protected function seoDescriptionFields(): array
    {
        return ['excerpt', 'summary'];
    }
}
