<?php

namespace App\Models;

use App\Enums\AssetKind;
use App\Enums\EmbedProvider;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\LogsModelActivity;
use App\Settings\GeneralSettings;
use App\Settings\MediaSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * One file in the central media library (image, video, document or embed).
 *
 * @property int $id
 * @property int|null $folder_id
 * @property AssetKind $kind
 * @property string|null $original_name
 * @property string|null $mime
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration
 * @property string|null $external_url
 * @property EmbedProvider|null $provider
 * @property string|null $external_id
 * @property int|null $poster_asset_id
 * @property array<string, string>|null $alt
 * @property array<string, string>|null $title
 * @property array<string, string>|null $caption
 * @property string $focal
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read MediaFolder|null $folder
 * @property-read Asset|null $poster
 * @property-read User|null $uploader
 */
class Asset extends Model implements HasMedia
{
    use ClearsSiteCache, HasTranslations, InteractsWithMedia, LogsModelActivity, SoftDeletes;

    /** Fixed conversion widths (WebP). */
    public const WIDTHS = ['sm' => 640, 'md' => 1024, 'lg' => 1600, 'xl' => 2048];

    protected $fillable = [
        'folder_id', 'kind', 'original_name', 'mime', 'size', 'width', 'height', 'duration',
        'external_url', 'provider', 'external_id', 'poster_asset_id', 'alt', 'title', 'caption',
        'focal', 'uploaded_by',
    ];

    /** @var list<string> */
    public array $translatable = ['alt', 'title', 'caption'];

    protected function casts(): array
    {
        return [
            'kind' => AssetKind::class,
            'provider' => EmbedProvider::class,
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media === null || ! str_starts_with((string) $media->mime_type, 'image/') || $media->mime_type === 'image/svg+xml') {
            return;
        }

        $quality = app(MediaSettings::class)->webp_quality;
        $position = match ($this->focal) {
            'top' => CropPosition::Top,
            'bottom' => CropPosition::Bottom,
            default => CropPosition::Center,
        };

        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->crop(320, 240, $position)
            ->format('webp')
            ->quality($quality);

        foreach (self::WIDTHS as $name => $width) {
            $this->addMediaConversion($name)
                ->nonQueued()
                ->fit(Fit::Max, $width, $width * 4)
                ->format('webp')
                ->quality($quality);
        }
    }

    /**
     * @return BelongsTo<MediaFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'poster_asset_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @param  Builder<Asset>  $query
     */
    public function scopeOfKind(Builder $query, AssetKind $kind): void
    {
        $query->where('kind', $kind->value);
    }

    /**
     * @param  Builder<Asset>  $query
     */
    public function scopeMissingAlt(Builder $query, string $locale): void
    {
        $query->where('kind', AssetKind::Image->value)
            ->where(fn (Builder $q) => $q->whereNull('alt')
                ->orWhereNull('alt->'.$locale)
                ->orWhere('alt->'.$locale, ''));
    }

    public function file(): ?Media
    {
        return $this->getFirstMedia('file');
    }

    public function isImage(): bool
    {
        return $this->kind === AssetKind::Image;
    }

    public function url(string $conversion = ''): ?string
    {
        if ($this->kind === AssetKind::Embed) {
            return $this->external_url;
        }

        $media = $this->file();

        if ($media === null) {
            return null;
        }

        if ($conversion !== '' && $media->hasGeneratedConversion($conversion)) {
            return $media->getUrl($conversion);
        }

        return $media->getUrl();
    }

    /**
     * Real pixel width of a conversion (conversions never upscale).
     */
    public function conversionWidth(string $conversion): ?int
    {
        if ($this->width === null) {
            return self::WIDTHS[$conversion] ?? null;
        }

        return min($this->width, self::WIDTHS[$conversion] ?? $this->width);
    }

    public function altText(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($this->getTranslation('alt', $locale, false)
            ?: $this->getTranslation('alt', 'ar', false)
            ?: $this->getTranslation('title', $locale, false)
            ?: '');
    }

    public function titleText(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($this->getTranslation('title', $locale, false)
            ?: $this->getTranslation('title', 'ar', false)
            ?: pathinfo((string) $this->original_name, PATHINFO_FILENAME));
    }

    public function embedUrl(bool $autoplay = true): ?string
    {
        if ($this->kind !== AssetKind::Embed || $this->external_id === null) {
            return null;
        }

        return match ($this->provider) {
            EmbedProvider::Youtube => 'https://www.youtube-nocookie.com/embed/'.$this->external_id.($autoplay ? '?autoplay=1&rel=0' : '?rel=0'),
            EmbedProvider::Vimeo => 'https://player.vimeo.com/video/'.$this->external_id.($autoplay ? '?autoplay=1' : ''),
            default => null,
        };
    }

    /**
     * Places where the asset is used (rows of `assetables` + SEO OG images).
     *
     * @return Collection<int, \stdClass>
     */
    public function usages(): Collection
    {
        $rows = DB::table('assetables')
            ->where('asset_id', $this->getKey())
            ->get(['assetable_type', 'assetable_id', 'collection']);

        $seo = DB::table('seo_meta')->where('og_asset_id', $this->getKey())
            ->get(['seoable_type as assetable_type', 'seoable_id as assetable_id'])
            ->map(function (object $row): object {
                $row->collection = 'og_image';

                return $row;
            });

        $posters = DB::table('assets')->where('poster_asset_id', $this->getKey())->whereNull('deleted_at')
            ->pluck('id')
            ->map(fn ($id) => (object) ['assetable_type' => (new Asset)->getMorphClass(), 'assetable_id' => $id, 'collection' => 'poster']);

        $settings = collect();
        $references = [
            'logo' => app(GeneralSettings::class)->logo_asset_id,
            'logo_white' => app(GeneralSettings::class)->logo_white_asset_id,
            'favicon' => app(GeneralSettings::class)->favicon_asset_id,
            'default_og' => app(SeoSettings::class)->default_og_asset_id,
        ];

        foreach ($references as $key => $id) {
            if ((int) $id === (int) $this->getKey()) {
                $settings->push((object) ['assetable_type' => 'settings', 'assetable_id' => 0, 'collection' => $key]);
            }
        }

        return $rows->concat($seo)->concat($posters)->concat($settings)->values();
    }

    public function isUsed(): bool
    {
        return $this->usages()->isNotEmpty();
    }
}
