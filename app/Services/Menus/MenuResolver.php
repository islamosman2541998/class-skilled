<?php

namespace App\Services\Menus;

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\MenuItemEntry;
use App\Services\SiteCache;
use App\Support\LinkResolver;
use App\Support\LocalizedRoute;
use App\Support\Translate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the public menu trees (header, top bar, footer columns, legal,
 * mobile) for a locale: inherited labels, hidden/unpublished targets skipped,
 * dynamic groups expanded (manual / all / featured / latest + limit).
 * Cached per location + locale; `active` flags are applied per request.
 */
class MenuResolver
{
    /** System pages and their UI-string label keys. */
    public const SYSTEM_LABELS = [
        'home' => 'nav.home',
        'services.index' => 'nav.services',
        'projects.index' => 'nav.projects',
        'posts.index' => 'nav.posts',
        'careers.index' => 'nav.careers',
        'quote' => 'nav.quote',
        'contact' => 'nav.contact',
        'faq' => 'nav.faq',
        'search' => 'nav.search',
    ];

    /** dynamic source => link-index type */
    public const SOURCE_TYPES = [
        'services' => 'service',
        'projects' => 'project',
        'project_categories' => 'project_category',
        'posts' => 'post',
        'post_categories' => 'post_category',
        'pages' => 'page',
    ];

    public function __construct(
        private readonly SiteCache $cache,
        private readonly LinkIndex $index,
    ) {}

    /**
     * @return list<MenuNode>
     */
    public function forLocation(MenuLocation $location, ?string $locale = null, bool $markActive = true): array
    {
        $locale ??= app()->getLocale();
        $nodes = $this->cache->remember('menu.'.$location->value.'.'.$locale, fn () => $this->build($location, $locale));

        if (! $markActive) {
            return $nodes;
        }

        $current = '/'.trim(rawurldecode(request()->getPathInfo()), '/');
        $home = rtrim((string) parse_url(LocalizedRoute::url('home', $locale), PHP_URL_PATH), '/') ?: '/';

        return array_map(fn (MenuNode $node) => $node->markActive($current, $home), $nodes);
    }

    /**
     * Every location at once (site layout view composer).
     *
     * @return array<string, list<MenuNode>>
     */
    public function all(?string $locale = null): array
    {
        $menus = [];

        foreach (MenuLocation::cases() as $location) {
            $menus[$location->value] = $this->forLocation($location, $locale);
        }

        return $menus;
    }

    /**
     * Column title of a footer menu (menu name) in the locale.
     */
    public function title(MenuLocation $location, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->cache->remember('menu-title.'.$location->value.'.'.$locale, function () use ($location, $locale) {
            $name = DB::table('menus')->where('location', $location->value)->value('name');

            return Translate::pick(json_decode((string) $name, true) ?: [], $locale);
        });
    }

    /**
     * @return list<MenuNode>
     */
    public function build(MenuLocation $location, string $locale): array
    {
        $menu = DB::table('menus')->where('location', $location->value)->first(['id', 'is_active', 'use_header_menu']);

        if ($menu === null || ! $menu->is_active) {
            return [];
        }

        if ($location === MenuLocation::Mobile && $menu->use_header_menu) {
            return $this->build(MenuLocation::Header, $locale);
        }

        $items = MenuItem::query()->where('menu_id', $menu->id)->where('is_active', true)
            ->orderBy('sort')->orderBy('id')->get();

        $dynamicIds = $items->where('type', MenuItemType::DynamicGroup)->pluck('id')->all();
        $entries = $dynamicIds === []
            ? collect()
            : MenuItemEntry::query()->whereIn('menu_item_id', $dynamicIds)->orderBy('sort')->get()->groupBy('menu_item_id');

        $nodes = [];

        foreach ($items->whereNull('parent_id') as $item) {
            $node = $this->node($item, $locale, $entries->get($item->id) ?? collect());

            if ($node === null) {
                continue;
            }

            if ($item->type !== MenuItemType::DynamicGroup) {
                foreach ($items->where('parent_id', $item->id) as $child) {
                    $childNode = $this->node($child, $locale, $entries->get($child->id) ?? collect(), true);

                    if ($childNode !== null) {
                        $node->children[] = $childNode;
                    }
                }
            }

            // Headings exist only to hold children.
            if ($item->type === MenuItemType::Heading && $node->children === []) {
                continue;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @param  Collection<int, MenuItemEntry>  $entries
     */
    private function node(MenuItem $item, string $locale, $entries, bool $isChild = false): ?MenuNode
    {
        $custom = Translate::pick($item->getTranslations('label'), $locale, false) ?: Translate::pick($item->getTranslations('label'), 'ar');
        $base = ['newTab' => $item->target_blank, 'visibility' => $item->visibility->value];

        switch ($item->type) {
            case MenuItemType::Home:
            case MenuItemType::SystemPage:
                $route = $item->type === MenuItemType::Home ? 'home' : (string) $item->system_route;

                if (! isset(self::SYSTEM_LABELS[$route])) {
                    return null;
                }

                return new MenuNode(label: $custom ?: __(self::SYSTEM_LABELS[$route], [], $locale), url: LocalizedRoute::url($route, $locale), newTab: $base['newTab'], visibility: $base['visibility']);

            case MenuItemType::Page:
            case MenuItemType::Model:
                $target = $this->target((string) $item->linkable_type, (int) $item->linkable_id, $locale);

                return $target === null ? null : new MenuNode(label: $custom ?: $target['label'], url: $target['url'], description: $target['description'], newTab: $base['newTab'], visibility: $base['visibility']);

            case MenuItemType::External:
                $url = LinkResolver::url(['type' => 'external', 'url' => (string) $item->url]);

                return $url === null || $custom === '' ? null : new MenuNode(label: $custom, url: $url, newTab: $base['newTab'], visibility: $base['visibility']);

            case MenuItemType::Heading:
                return $custom === '' ? null : new MenuNode(label: $custom, newTab: $base['newTab'], visibility: $base['visibility']);

            case MenuItemType::DynamicGroup:
                return $this->dynamic($item, $locale, $entries, $custom, $base, $isChild);
        }
    }

    /**
     * @param  Collection<int, MenuItemEntry>  $entries
     * @param  array{newTab: bool, visibility: string}  $base
     */
    private function dynamic(MenuItem $item, string $locale, $entries, string $label, array $base, bool $isChild): ?MenuNode
    {
        $source = $item->dynamic_source?->value;
        $type = self::SOURCE_TYPES[$source] ?? null;

        if ($type === null) {
            return null;
        }

        $ownUrl = isset(self::SYSTEM_LABELS[(string) $item->system_route]) ? LocalizedRoute::url((string) $item->system_route, $locale) : null;
        $label = $label ?: ($item->system_route ? __(self::SYSTEM_LABELS[(string) $item->system_route] ?? 'nav.home', [], $locale) : '');

        if ($label === '') {
            return null;
        }

        $index = $this->index->of($type);
        $limit = max(1, min(30, (int) ($item->dynamic_limit ?: 8)));

        $ids = match ($item->dynamic_mode ?? DataSource::All) {
            DataSource::Manual => $entries->where('entry_type', $type)->pluck('entry_id')->map(fn ($id) => (int) $id)->all(),
            DataSource::Featured => collect($index)->filter(fn ($row) => $row['featured'])->sortBy('sort')->keys()->all(),
            DataSource::Latest => collect($index)->sortByDesc(fn (array $row, int $id) => sprintf('%012d%010d', $row['date'], $id))->keys()->all(),
            DataSource::All => collect($index)->sortBy([['sort', 'asc']])->keys()->all(),
        };

        $children = [];

        foreach ($ids as $id) {
            if (count($children) >= $limit) {
                break;
            }

            $target = $this->target($type, (int) $id, $locale, $item->show_descriptions);

            if ($target !== null) {
                $children[] = new MenuNode(label: $target['label'], url: $target['url'], description: $target['description']);
            }
        }

        if ($children === [] && $ownUrl === null) {
            return null;
        }

        // Inside a footer column (child level) a group flattens to its items.
        return new MenuNode(
            label: $label,
            url: $ownUrl,
            newTab: $base['newTab'],
            visibility: $base['visibility'],
            children: $children,
            isDynamic: true,
            viewAll: $item->show_all_link && $ownUrl ? ['label' => __('site.view_all', [], $locale).' '.$label, 'url' => $ownUrl] : null,
        );
    }

    /**
     * @return array{label: string, url: string, description: string|null}|null
     */
    private function target(string $type, int $id, string $locale, bool $withDescription = true): ?array
    {
        $row = $this->index->of($type)[$id] ?? null;

        if ($row === null || ! $row['public']) {
            return null;
        }

        $slug = $row['slug'][$locale] ?? null;
        $title = $row['title'][$locale] ?? null;

        if (blank($slug) || blank($title)) {
            return null; // not translated into this locale
        }

        return [
            'label' => (string) $title,
            'url' => LocalizedRoute::url(LinkIndex::TYPES[$type][1], $locale, ['slug' => $slug]),
            'description' => $withDescription ? (Translate::pick($row['summary'], $locale, false) ?: null) : null,
        ];
    }

    /**
     * Static description of an item for the dashboard tree (type + target).
     */
    public static function describe(MenuItem $item): string
    {
        return match ($item->type) {
            MenuItemType::DynamicGroup => ($item->dynamic_mode === DataSource::Manual
                ? __('admin.menus.manual_count', ['count' => $item->entries->count()])
                : __('admin.menus.auto').': '.($item->dynamic_mode?->label() ?? '')).' · '.($item->dynamic_source?->label() ?? DynamicSource::Services->label()),
            MenuItemType::SystemPage, MenuItemType::Home => (string) __('admin.link.routes.'.($item->type === MenuItemType::Home ? 'home' : $item->system_route)),
            MenuItemType::External => (string) $item->url,
            default => '',
        };
    }
}
