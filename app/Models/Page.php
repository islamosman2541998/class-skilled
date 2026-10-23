<?php

namespace App\Models;

use App\Enums\HeroMediaType;
use App\Models\Concerns\ClearsSiteCache;
use App\Models\Concerns\HasAssets;
use App\Models\Concerns\HasBlocks;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslatedSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * Custom page built from blocks (+ main media and three attachment groups).
 *
 * Asset collections: hero_image, hero_mobile_image, hero_video, hero_pdf, att_files, att_videos, att_images, blocks.
 *
 * @property int $id
 * @property array<string, string> $title
 * @property array<string, string> $slug
 * @property array<string, string>|null $excerpt
 * @property HeroMediaType $hero_type
 * @property array<string, array<string, mixed>>|null $attachments_config
 * @property int|null $parent_id
 * @property string $template
 * @property bool $show_in_sitemap
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Page|null $parent
 * @property-read Collection<int, Page> $children
 */
class Page extends Model
{
    use ClearsSiteCache, HasAssets, HasBlocks, HasSeo, HasTranslatedSlug, HasTranslations, LogsModelActivity, Publishable, Sortable;

    public const ATTACHMENT_GROUPS = ['files', 'videos', 'images'];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'hero_type', 'blocks', 'attachments_config', 'parent_id',
        'template', 'status', 'published_at', 'show_in_sitemap', 'sort',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'excerpt'];

    protected function casts(): array
    {
        return [
            'hero_type' => HeroMediaType::class,
            'attachments_config' => 'array',
            'show_in_sitemap' => 'boolean',
        ];
    }

    public function publicRouteName(): string
    {
        return 'pages.show';
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * @return HasMany<Page, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    /**
     * Attachment group settings with defaults:
     * `{visible: bool, title: {ar, en}, display: string, columns?: int}`.
     *
     * @return array<string, mixed>
     */
    public function attachmentGroup(string $group): array
    {
        $defaults = [
            'files' => ['visible' => true, 'title' => ['ar' => '', 'en' => ''], 'display' => 'list'],
            'videos' => ['visible' => true, 'title' => ['ar' => '', 'en' => ''], 'display' => 'grid'],
            'images' => ['visible' => true, 'title' => ['ar' => '', 'en' => ''], 'display' => 'grid', 'columns' => 3],
        ];

        return array_replace($defaults[$group] ?? [], (array) ($this->attachments_config[$group] ?? []));
    }

    /**
     * Breadcrumb trail from the root page to this one.
     *
     * @return list<Page>
     */
    public function ancestors(): array
    {
        $trail = [];
        $current = $this->parent_id ? Page::query()->find($this->parent_id) : null;
        $guard = 0;

        while ($current !== null && $guard++ < 5) {
            array_unshift($trail, $current);
            $current = $current->parent_id ? Page::query()->find($current->parent_id) : null;
        }

        return $trail;
    }
}
