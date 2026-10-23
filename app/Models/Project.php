<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\HasBlocks;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Sortable;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Asset collections: cover, gallery, before_after (meta: {pair, side}), videos, blocks.
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property int|null $category_id
 * @property array<string, string>|null $location
 * @property string|null $area_m2
 * @property array<string, string>|null $duration
 * @property int|null $year
 * @property ProjectStatus $project_status
 * @property array<string, string>|null $client_name
 * @property bool $show_client
 * @property array<string, string>|null $excerpt
 * @property array<string, list<string>>|null $scope
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProjectCategory|null $category
 * @property-read Collection<int, Service> $services
 */
class Project extends Model
{
    use ClearsSiteCache, HasAssets, HasBlocks, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Publishable, Sortable;

    protected $fillable = [
        'title', 'slug', 'category_id', 'location', 'area_m2', 'duration', 'year', 'project_status',
        'client_name', 'show_client', 'excerpt', 'blocks', 'scope', 'is_featured', 'status', 'published_at', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'location', 'duration', 'client_name', 'excerpt'];

    protected function casts(): array
    {
        return [
            'project_status' => ProjectStatus::class,
            'area_m2' => 'decimal:2',
            'year' => 'integer',
            'scope' => 'array',
            'show_client' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function publicRouteName(): string
    {
        return 'projects.show';
    }

    /**
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * @return list<string>
     */
    public function workScope(?string $locale = null): array
    {
        return Translate::list($this->scope, $locale);
    }

    /**
     * Before/after pairs from the `before_after` collection (meta: pair index + side).
     *
     * @return list<array{before: Asset, after: Asset}>
     */
    public function beforeAfterPairs(): array
    {
        $pairs = [];

        foreach ($this->assetsIn('before_after') as $asset) {
            $meta = json_decode((string) $asset->getRelationValue('pivot')?->meta, true) ?: [];
            $pairs[(int) ($meta['pair'] ?? 0)][($meta['side'] ?? 'before') === 'after' ? 'after' : 'before'] = $asset;
        }

        ksort($pairs);

        return array_values(array_filter($pairs, fn ($pair) => isset($pair['before'], $pair['after'])));
    }

    public function areaFormatted(): ?string
    {
        return $this->area_m2 === null ? null : rtrim(rtrim(number_format((float) $this->area_m2, 2, '.', ','), '0'), '.');
    }
}
