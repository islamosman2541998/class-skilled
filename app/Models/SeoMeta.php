<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\SiteCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $seoable_type
 * @property int $seoable_id
 * @property array<string, string>|null $title
 * @property array<string, string>|null $description
 * @property array<string, string>|null $canonical
 * @property int|null $og_asset_id
 * @property string $robots
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Asset|null $ogAsset
 * @property-read Model $seoable
 */
class SeoMeta extends Model
{
    use HasTranslations, LogsModelActivity;

    protected $table = 'seo_meta';

    protected $fillable = ['title', 'description', 'canonical', 'og_asset_id', 'robots'];

    /** @var list<string> */
    public array $translatable = ['title', 'description', 'canonical'];

    protected static function booted(): void
    {
        static::saved(fn () => app(SiteCache::class)->flush());
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function ogAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_asset_id');
    }
}
