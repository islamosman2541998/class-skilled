<?php

namespace App\Services\Menus;

use App\Models\ContentModel;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Services\SiteCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Compact, cached index of every linkable item (titles, slugs, summaries,
 * public state, featured flag, order) so menus resolve without loading models.
 * One cache entry per type, rebuilt after any content change (SiteCache flush).
 */
class LinkIndex
{
    /** morph alias => [model, public route, title field, summary field|null] */
    public const TYPES = [
        'page' => [Page::class, 'pages.show', 'title', 'excerpt'],
        'service' => [Service::class, 'services.show', 'title', 'summary'],
        'project' => [Project::class, 'projects.show', 'title', 'excerpt'],
        'project_category' => [ProjectCategory::class, 'projects.category', 'name', null],
        'post' => [Post::class, 'posts.show', 'title', 'excerpt'],
        'post_category' => [PostCategory::class, 'posts.category', 'name', null],
    ];

    /** @var array<string, array<int, array<string, mixed>>> */
    private array $memo = [];

    public function __construct(private readonly SiteCache $cache) {}

    /**
     * @return array<int, array{title: array<string, string>, slug: array<string, string>, summary: array<string, string>, public: bool, featured: bool, sort: int, date: int}>
     */
    public function of(string $type): array
    {
        if (! isset(self::TYPES[$type])) {
            return [];
        }

        // Memo keyed by the versioned cache key: a SiteCache flush invalidates it too.
        $key = $this->cache->key('link-index.'.$type);

        return $this->memo[$key] ??= $this->cache->remember('link-index.'.$type, fn () => $this->build($type));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function build(string $type): array
    {
        [$class, , $titleField, $summaryField] = self::TYPES[$type];
        $index = [];

        foreach ($class::query()->get() as $model) {
            /** @var ContentModel $model */
            $attributes = $model->getAttributes();
            $publishedAt = array_key_exists('published_at', $attributes) ? $model->getAttribute('published_at') : null;
            $published = array_key_exists('status', $attributes)
                ? $model->getAttribute('status')?->value === 'published' && $publishedAt !== null && ! $publishedAt->isFuture()
                : true;
            $active = array_key_exists('is_active', $attributes) ? (bool) $model->getAttribute('is_active') : true;

            $index[(int) $model->getKey()] = [
                'title' => (array) $model->getTranslations($titleField),
                'slug' => (array) $model->getTranslations('slug'),
                'summary' => $summaryField ? (array) $model->getTranslations($summaryField) : [],
                'public' => $published && $active,
                'featured' => (bool) ($attributes['is_featured'] ?? false),
                'sort' => (int) ($attributes['sort'] ?? 0),
                'date' => (int) ($publishedAt ?? $model->getAttribute('created_at'))?->getTimestamp(),
            ];
        }

        return $index;
    }
}
