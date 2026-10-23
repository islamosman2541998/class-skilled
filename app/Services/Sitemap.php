<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Support\LocalizedRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap as SitemapDocument;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

/**
 * XML sitemaps written to storage/app/sitemaps on content change (no cron):
 * an index plus one file per content type, each URL with hreflang alternates.
 */
class Sitemap
{
    public const TYPES = ['static', 'pages', 'services', 'projects', 'project-categories', 'posts', 'post-categories', 'careers'];

    private bool $scheduled = false;

    public function regenerateLater(): void
    {
        if ($this->scheduled) {
            return;
        }

        $this->scheduled = true;

        app()->terminating(function (): void {
            $this->scheduled = false;
            rescue(fn () => $this->regenerate(), report: true);
        });
    }

    public function regenerate(): void
    {
        File::ensureDirectoryExists($this->directory());
        $index = SitemapIndex::create();

        foreach (self::TYPES as $type) {
            $document = $this->build($type);
            $document->writeToFile($this->path($type));
            $index->add(url('/sitemaps/'.$type.'.xml'));
        }

        $index->writeToFile($this->path());
    }

    public function path(?string $type = null): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.($type === null ? 'sitemap' : $type).'.xml';
    }

    public function directory(): string
    {
        return storage_path('app/sitemaps');
    }

    public function build(string $type): SitemapDocument
    {
        $sitemap = SitemapDocument::create();
        $locales = array_keys(config('laravellocalization.supportedLocales'));

        if ($type === 'static') {
            foreach (['home', 'services.index', 'projects.index', 'posts.index', 'careers.index', 'quote', 'contact', 'faq'] as $route) {
                $urls = [];

                foreach ($locales as $locale) {
                    $urls[$locale] = LocalizedRoute::url($route, $locale);
                }

                $this->addWithAlternates($sitemap, $urls, null);
            }

            return $sitemap;
        }

        $query = match ($type) {
            'pages' => Page::query()->published()->where('show_in_sitemap', true),
            'services' => Service::query()->where('is_active', true),
            'projects' => Project::query()->published(),
            'project-categories' => ProjectCategory::query()->where('is_active', true),
            'posts' => Post::query()->published(),
            'post-categories' => PostCategory::query(),
            'careers' => JobPost::query()->open(),
            default => null,
        };

        if ($query === null) {
            return $sitemap;
        }

        $query->with('seo')->each(function (Model $model) use ($sitemap, $locales): void {
            if ($model->seoNoindex()) {
                return;
            }

            $urls = [];

            foreach ($locales as $locale) {
                if ($model->hasLocale($locale)) {
                    $urls[$locale] = $model->localizedUrl($locale);
                }
            }

            $this->addWithAlternates($sitemap, array_filter($urls), $model->getAttribute('updated_at'));
        });

        return $sitemap;
    }

    /**
     * @param  array<string, string|null>  $urls  locale => url
     */
    private function addWithAlternates(SitemapDocument $sitemap, array $urls, mixed $updatedAt): void
    {
        $urls = array_map(fn ($url) => static::encode((string) $url), $urls);

        foreach ($urls as $url) {
            $tag = Url::create($url);

            if ($updatedAt instanceof \DateTimeInterface) {
                $tag->setLastModificationDate($updatedAt);
            }

            if (count($urls) > 1) {
                foreach ($urls as $alternateLocale => $alternateUrl) {
                    $tag->addAlternate($alternateUrl, $alternateLocale);
                }
            }

            $sitemap->add($tag);
        }
    }

    /**
     * Percent-encode the path (Arabic slugs) as the sitemap protocol requires.
     */
    public static function encode(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['host'])) {
            return $url;
        }

        $path = implode('/', array_map(fn (string $segment) => rawurlencode(rawurldecode($segment)), explode('/', $parts['path'] ?? '')));

        return ($parts['scheme'] ?? 'https').'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
