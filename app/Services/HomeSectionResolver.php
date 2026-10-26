<?php

namespace App\Services;

use App\Enums\DataSource;
use App\Enums\SectionBackground;
use App\Enums\SectionType;
use App\Models\Asset;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Concerns\Sortable;
use App\Models\HomeSection;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Support\LinkResolver;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Turns the visible home sections into view models for one locale:
 * texts resolved, links resolved (hidden when the target is gone), assets and
 * list items eager loaded in batches. Cached per locale in SiteCache.
 */
class HomeSectionResolver
{
    public function __construct(private readonly SiteCache $cache) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function forLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $this->cache->remember('home.sections.'.$locale, fn () => $this->resolve($locale));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolve(string $locale, bool $includeHidden = false): array
    {
        $sections = HomeSection::query()
            ->when(! $includeHidden, fn (Builder $q) => $q->where('is_visible', true))
            ->orderBy('sort')->orderBy('id')
            ->get();

        $assetIds = $sections->flatMap(fn (HomeSection $s) => $s->referencedAssetIds())->unique()->values()->all();
        /** @var Collection<int, Asset> $assets */
        $assets = $assetIds === [] ? new Collection : Asset::query()->with(['media', 'poster.media'])->whereIn('id', $assetIds)->get()->keyBy('id');

        $result = [];

        foreach ($sections as $section) {
            $view = $this->section($section, $locale, $assets);

            if ($view !== null) {
                $result[] = $view;
            }
        }

        return $result;
    }

    /**
     * @param  Collection<int, Asset>  $assets
     * @return array<string, mixed>|null
     */
    public function section(HomeSection $section, string $locale, Collection $assets): ?array
    {
        $data = $section->data ?? [];
        $asset = fn ($id) => is_numeric($id) ? $assets->get((int) $id) : null;
        $text = fn ($value) => Translate::pick($value, $locale);

        $base = [
            'id' => $section->id,
            'type' => $section->type->value,
            'view' => 'site.sections.'.str_replace('_', '-', $section->type->value),
            'background' => $section->background->value,
            'bg_image' => $section->background === SectionBackground::DarkPhoto ? $asset($data['bg_asset_id'] ?? null) : null,
            'title' => (string) ($section->getTranslation('title', $locale, false) ?: $section->getTranslation('title', 'ar', false)),
            'subtitle' => (string) ($section->getTranslation('subtitle', $locale, false) ?: $section->getTranslation('subtitle', 'ar', false)),
            'cta' => LinkResolver::button(['label' => $section->getTranslations('cta_label'), 'link' => $section->cta_link], $locale),
            'display' => ($data['display'] ?? 'slider') === 'grid' ? 'grid' : 'slider',
            'autoplay' => (bool) ($data['autoplay'] ?? true),
        ];

        $specific = match ($section->type) {
            SectionType::Hero => $this->hero($data, $locale, $asset, $text),
            SectionType::About => [
                'text' => $text($data['text'] ?? []),
                'image' => $asset($data['image_asset_id'] ?? null),
            ],
            SectionType::FeatureBand => [
                'bullets' => array_values(array_filter(array_map($text, (array) ($data['bullets'] ?? [])))),
                'images' => array_values(array_filter(array_map($asset, (array) ($data['image_asset_ids'] ?? [])))),
            ],
            SectionType::WhyUs => ['items' => $this->titleTexts($data['items'] ?? [], $text)],
            SectionType::Process => ['steps' => $this->titleTexts($data['steps'] ?? [], $text)],
            SectionType::Stats => [
                'count_up' => (bool) ($data['count_up'] ?? true),
                'items' => array_values(array_filter(array_map(fn ($item) => filled($item['value'] ?? null) ? [
                    'value' => (string) $item['value'],
                    'suffix' => $text($item['suffix'] ?? []),
                    'label' => $text($item['label'] ?? []),
                ] : null, (array) ($data['items'] ?? [])))),
            ],
            SectionType::Cta => [
                'primary' => LinkResolver::button($data['primary'] ?? null, $locale),
                'secondary' => LinkResolver::button($data['secondary'] ?? null, $locale),
            ],
            SectionType::RichText => ['html' => $text($data['html'] ?? [])],
            SectionType::Services, SectionType::Projects, SectionType::Posts,
            SectionType::Testimonials, SectionType::Clients, SectionType::Certificates => [
                'items' => $this->items($section),
                'show_filters' => (bool) ($data['show_filters'] ?? false),
            ],
        };

        $view = array_merge($base, $specific);

        return $this->isEmpty($view) ? null : $view;
    }

    /**
     * @param  array<string, mixed>  $view
     */
    private function isEmpty(array $view): bool
    {
        return match ($view['type']) {
            'hero' => $view['slides'] === [],
            'why_us', 'stats' => $view['items'] === [],
            'process' => $view['steps'] === [],
            'rich_text' => trim(strip_tags($view['html'])) === '',
            'services', 'projects', 'posts', 'testimonials', 'clients', 'certificates' => $view['items']->isEmpty(),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function hero(array $data, string $locale, callable $asset, callable $text): array
    {
        $slides = [];

        foreach ((array) ($data['slides'] ?? []) as $slide) {
            $image = $asset($slide['image_asset_id'] ?? null);
            $video = $asset($slide['video_asset_id'] ?? null);

            if ($image === null && $video === null) {
                continue;
            }

            $slides[] = [
                'image' => $image,
                'mobile_image' => $asset($slide['mobile_image_asset_id'] ?? null),
                'video' => $video,
                'title' => $text($slide['title'] ?? []),
                'text' => $text($slide['text'] ?? []),
                'primary' => LinkResolver::button($slide['primary'] ?? null, $locale),
                'secondary' => LinkResolver::button($slide['secondary'] ?? null, $locale),
                'caption' => $text($slide['caption'] ?? []),
                'overlay' => max(0, min(100, (int) ($slide['overlay'] ?? 45))) / 100,
            ];
        }

        return [
            'slides' => $slides,
            'autoplay_seconds' => max(4, min(10, (int) ($data['autoplay'] ?? 6))),
            'show_arrows' => (bool) ($data['show_arrows'] ?? true),
            'show_progress' => (bool) ($data['show_progress'] ?? true),
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{title: string, text: string}>
     */
    private function titleTexts(array $items, callable $text): array
    {
        $out = [];

        foreach ($items as $item) {
            $title = $text($item['title'] ?? []);

            if ($title !== '') {
                $out[] = ['title' => $title, 'text' => $text($item['text'] ?? [])];
            }
        }

        return $out;
    }

    /**
     * Items of a list section according to its data source and limit.
     *
     * @return \Illuminate\Support\Collection<int, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function items(HomeSection $section): \Illuminate\Support\Collection
    {
        $limit = max(1, min(24, (int) ($section->items_limit ?: 8)));

        [$query, $featured, $latest] = match ($section->type) {
            SectionType::Services => [Service::query()->where('is_active', true)->with('assets.media'), 'is_featured', 'id'],
            SectionType::Projects => [Project::query()->published()->with(['category', 'assets.media']), 'is_featured', 'published_at'],
            SectionType::Posts => [Post::query()->published()->with(['category', 'assets.media']), 'is_featured', 'published_at'],
            SectionType::Testimonials => [Testimonial::query()->where('is_active', true)->with(['assets.media', 'project']), null, 'id'],
            SectionType::Clients => [Client::query()->where('is_active', true)->with('assets.media'), null, 'id'],
            SectionType::Certificates => [Certificate::query()->where('show_on_site', true)->with('assets.media'), null, 'id'],
            default => [null, null, null],
        };

        if ($query === null) {
            return new Collection;
        }

        $orderable = in_array(Sortable::class, class_uses_recursive($query->getModel()), true);

        switch ($section->source) {
            case DataSource::Manual:
                $ids = $section->manualIds();

                if ($ids === []) {
                    return new Collection;
                }

                $found = $query->whereIn($query->getModel()->getQualifiedKeyName(), $ids)->get()->keyBy('id');

                return new Collection(array_values(array_filter(array_map(fn (int $id) => $found->get($id), array_slice($ids, 0, $limit)))));
            case DataSource::Featured:
                if ($featured !== null) {
                    $query->where($featured, true);
                }
                $orderable ? $query->orderBy('sort') : $query->latest($latest);
                break;
            case DataSource::Latest:
                $query->orderByDesc($latest);
                break;
            default:
                $orderable ? $query->orderBy('sort')->orderBy('id') : $query->latest($latest);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Dashboard summary line of a section (e.g. "Manual: 8 services").
     */
    public function summary(HomeSection $section): string
    {
        $data = $section->data ?? [];

        return match ($section->type) {
            SectionType::Hero => __('admin.home.summary.slides', ['count' => count((array) ($data['slides'] ?? []))]),
            SectionType::WhyUs, SectionType::Stats => __('admin.home.summary.items', ['count' => count((array) ($data['items'] ?? []))]),
            SectionType::Process => __('admin.home.summary.steps', ['count' => count((array) ($data['steps'] ?? []))]),
            SectionType::About => __('admin.home.summary.images', ['count' => filled($data['image_asset_id'] ?? null) ? 1 : 0]),
            SectionType::FeatureBand => __('admin.home.summary.images', ['count' => count((array) ($data['image_asset_ids'] ?? []))]),
            SectionType::Cta, SectionType::RichText => (string) $section->getTranslation('subtitle', 'ar', false),
            default => $section->source->label().' · '.__('admin.home.summary.count', ['count' => $this->items($section)->count()]),
        };
    }
}
