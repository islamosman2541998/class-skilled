<?php

namespace App\Livewire\Admin\Navigation;

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Enums\Visibility;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Menus\LinkIndex;
use App\Services\Menus\MenuResolver;
use App\Support\LinkOptions;
use App\Support\LinkResolver;
use App\Support\Translate;
use Database\Seeders\DefaultMenus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Menu builder: one tab per location, a nested (depth 2) drag & drop tree,
 * an "add item" panel and an item side panel (labels, target, dynamic groups).
 *
 * @property-read Menu $menu
 * @property-read array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>} $options
 */
#[Layout('layouts.admin')]
class Menus extends Component
{
    use WithToasts;
    use WithTranslatableForm;

    #[Url(as: 'location', except: 'header')]
    public string $location = 'header';

    public string $addTab = 'pages';

    public string $search = '';

    /** @var list<string> */
    public array $pickPages = [];

    /** @var array{label: array{ar: string, en: string}, url: string} */
    public array $external = ['label' => ['ar' => '', 'en' => ''], 'url' => ''];

    /** @var array{ar: string, en: string} */
    public array $heading = ['ar' => '', 'en' => ''];

    public ?int $editing = null;

    /** @var array<string, mixed> */
    public array $item = [];

    /** @var array{ar: string, en: string} */
    public array $menuName = ['ar' => '', 'en' => ''];

    public function mount(): void
    {
        $this->authorize('viewAny', Menu::class);
        $this->loadMenuName();
    }

    public function updatedLocation(): void
    {
        if (MenuLocation::tryFrom($this->location) === null) {
            $this->location = 'header';
        }

        $this->editing = null;
        $this->item = [];
        unset($this->menu);
        $this->loadMenuName();
    }

    private function loadMenuName(): void
    {
        $this->menuName = Translate::pair($this->menu->getTranslations('name'));
    }

    #[Computed]
    public function menu(): Menu
    {
        $location = MenuLocation::tryFrom($this->location) ?? MenuLocation::Header;

        return Menu::query()->firstOrCreate(
            ['location' => $location->value],
            ['name' => DefaultMenus::name($location), 'is_active' => true],
        )->load(['items.entries', 'items.linkable']);
    }

    /**
     * @return array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>}
     */
    #[Computed]
    public function options(): array
    {
        return LinkOptions::all();
    }

    // ---------- Adding ----------

    private function nextSort(): int
    {
        return (int) $this->menu->items()->whereNull('parent_id')->max('sort') + 1;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function create(array $attributes): MenuItem
    {
        $this->authorize('create', MenuItem::class);

        $item = $this->menu->items()->create(array_merge([
            'visibility' => Visibility::All,
            'is_active' => true,
            'sort' => $this->nextSort(),
        ], $attributes));

        unset($this->menu);
        $this->toast('success', __('admin.menus.added'));

        return $item;
    }

    public function addPages(): void
    {
        foreach (array_map('intval', $this->pickPages) as $id) {
            $this->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $id]);
        }

        $this->pickPages = [];
    }

    public function addSystem(string $route): void
    {
        if (! in_array($route, LinkResolver::SYSTEM_ROUTES, true)) {
            return;
        }

        $this->create($route === 'home' ? ['type' => MenuItemType::Home] : ['type' => MenuItemType::SystemPage, 'system_route' => $route]);
    }

    public function addModel(string $type, int $id): void
    {
        if (! isset(LinkIndex::TYPES[$type])) {
            return;
        }

        $this->create(['type' => $type === 'page' ? MenuItemType::Page : MenuItemType::Model, 'linkable_type' => $type, 'linkable_id' => $id]);
    }

    public function addDynamic(): void
    {
        $item = $this->create([
            'type' => MenuItemType::DynamicGroup,
            'label' => ['ar' => __('nav.services', [], 'ar'), 'en' => __('nav.services', [], 'en')],
            'system_route' => 'services.index',
            'dynamic_source' => DynamicSource::Services,
            'dynamic_mode' => DataSource::All,
            'dynamic_limit' => 8,
            'show_all_link' => true,
            'show_descriptions' => true,
        ]);
        $this->edit($item->id);
    }

    public function addExternal(): void
    {
        $this->validate([
            'external.label.ar' => ['required', 'string', 'max:80'],
            'external.label.en' => ['nullable', 'string', 'max:80'],
            'external.url' => ['required', 'string', 'max:500', 'regex:#^(https?://|mailto:|tel:)#i'],
        ], [], ['external.label.ar' => __('admin.menus.label'), 'external.url' => __('admin.link.url')]);

        $this->create(['type' => MenuItemType::External, 'label' => array_filter(Translate::pair($this->external['label'])), 'url' => trim($this->external['url']), 'target_blank' => true]);
        $this->external = ['label' => ['ar' => '', 'en' => ''], 'url' => ''];
    }

    public function addHeading(): void
    {
        $this->validate(['heading.ar' => ['required', 'string', 'max:80'], 'heading.en' => ['nullable', 'string', 'max:80']], [], ['heading.ar' => __('admin.menus.label')]);
        $this->create(['type' => MenuItemType::Heading, 'label' => array_filter(Translate::pair($this->heading))]);
        $this->heading = ['ar' => '', 'en' => ''];
    }

    /**
     * Search results for the "specific item" tab.
     *
     * @return list<array{type: string, id: int, label: string, group: string}>
     */
    #[Computed]
    public function searchResults(): array
    {
        $term = trim($this->search);
        $results = [];

        foreach ($this->options['models'] as $type => $group) {
            foreach ($group['items'] as $id => $label) {
                if ($term === '' || mb_stripos($label, $term) !== false) {
                    $results[] = ['type' => $type, 'id' => (int) $id, 'label' => $label, 'group' => $group['label']];
                }
            }
        }

        return array_slice($results, 0, 30);
    }

    // ---------- Tree ----------

    /**
     * @param  list<array{id: int|string, parent: int|string|null, sort: int}>  $tree
     */
    public function saveTree(array $tree): void
    {
        $this->authorize('update', new MenuItem);
        $items = $this->menu->items->keyBy('id');
        $parents = [];

        foreach ($tree as $row) {
            $parents[(int) $row['id']] = $row['parent'] !== null ? (int) $row['parent'] : null;
        }

        foreach ($parents as $id => $parent) {
            $invalid = $parent !== null && (
                ! $items->has($parent)
                || ($parents[$parent] ?? null) !== null                                   // depth > 2
                || in_array($id, $parents, true)                                         // item with children nested
                || $items->get($parent)?->type === MenuItemType::DynamicGroup              // groups fill themselves
            );

            if ($invalid) {
                unset($this->menu);
                $this->toast('error', __('admin.menus.max_depth'));

                return;
            }
        }

        DB::transaction(function () use ($tree, $items): void {
            foreach ($tree as $row) {
                $item = $items->get((int) $row['id']);
                $item?->update(['parent_id' => $row['parent'] !== null ? (int) $row['parent'] : null, 'sort' => (int) $row['sort']]);
            }
        });

        unset($this->menu);
        $this->toast('success', __('admin.common.reordered'));
    }

    public function delete(int $id): void
    {
        $item = MenuItem::query()->where('menu_id', $this->menu->id)->findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        if ($this->editing === $id) {
            $this->editing = null;
        }

        unset($this->menu);
        $this->toast('success', __('admin.common.deleted'));
    }

    public function toggleActive(int $id): void
    {
        $item = MenuItem::query()->where('menu_id', $this->menu->id)->findOrFail($id);
        $this->authorize('update', $item);
        $item->update(['is_active' => ! $item->is_active]);
        unset($this->menu);
    }

    // ---------- Editing ----------

    public function edit(int $id): void
    {
        $item = MenuItem::query()->with('entries')->where('menu_id', $this->menu->id)->findOrFail($id);
        $this->editing = $item->id;
        $this->item = [
            'type' => $item->type->value,
            'label' => Translate::pair($item->getTranslations('label')),
            'system_route' => $item->system_route,
            'linkable' => $item->linkable_type ? $item->linkable_type.':'.$item->linkable_id : null,
            'url' => $item->url,
            'dynamic_source' => $item->dynamic_source->value ?? 'services',
            'dynamic_mode' => $item->dynamic_mode->value ?? 'all',
            'dynamic_limit' => $item->dynamic_limit ?? 8,
            'show_all_link' => $item->show_all_link,
            'show_descriptions' => $item->show_descriptions,
            'target_blank' => $item->target_blank,
            'visibility' => $item->visibility->value,
            'is_active' => $item->is_active,
            'entries' => $item->entries->pluck('entry_id')->map(fn ($id) => (int) $id)->all(),
            'entry_pick' => '',
        ];
        $this->resetErrorBag();
    }

    public function closeItem(): void
    {
        $this->editing = null;
        $this->item = [];
    }

    public function addEntry(): void
    {
        $id = (int) ($this->item['entry_pick'] ?? 0);

        if ($id > 0 && ! in_array($id, $this->item['entries'], true)) {
            $this->item['entries'][] = $id;
        }

        $this->item['entry_pick'] = '';
    }

    public function removeEntry(int $id): void
    {
        $this->item['entries'] = array_values(array_diff($this->item['entries'], [$id]));
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function sortEntries(array $ids): void
    {
        $this->item['entries'] = array_values(array_intersect(array_map('intval', $ids), $this->item['entries']));
    }

    public function updatedItem(mixed $value, string $key): void
    {
        if ($key === 'dynamic_source') {
            $this->item['entries'] = [];
        }
    }

    public function saveItem(): void
    {
        $item = MenuItem::query()->where('menu_id', $this->menu->id)->findOrFail((int) $this->editing);
        $this->authorize('update', $item);
        $type = $item->type;

        $rules = [
            ...$this->pairRules('item.label', in_array($type, [MenuItemType::External, MenuItemType::Heading], true), 80),
            'item.target_blank' => ['boolean'],
            'item.visibility' => ['required', Rule::in(Visibility::values())],
            'item.is_active' => ['boolean'],
        ];

        if ($type === MenuItemType::SystemPage) {
            $rules['item.system_route'] = ['required', Rule::in(array_diff(LinkResolver::SYSTEM_ROUTES, ['home']))];
        }

        if (in_array($type, [MenuItemType::Page, MenuItemType::Model], true)) {
            $rules['item.linkable'] = ['required', 'regex:/^[a-z_]+:\d+$/'];
        }

        if ($type === MenuItemType::External) {
            $rules['item.url'] = ['required', 'string', 'max:500', 'regex:#^(https?://|mailto:|tel:)#i'];
        }

        if ($type === MenuItemType::DynamicGroup) {
            $rules += [
                'item.dynamic_source' => ['required', Rule::in(DynamicSource::values())],
                'item.dynamic_mode' => ['required', Rule::in(DataSource::values())],
                'item.dynamic_limit' => ['required', 'integer', 'min:1', 'max:30'],
                'item.system_route' => ['nullable', Rule::in(LinkResolver::SYSTEM_ROUTES)],
                'item.show_all_link' => ['boolean'],
                'item.show_descriptions' => ['boolean'],
                'item.entries' => ['array'],
            ];
        }

        $this->validate($rules, [], ['item.label.ar' => __('admin.menus.label'), 'item.url' => __('admin.link.url')]);

        $attributes = [
            'label' => array_filter(Translate::pair($this->item['label'])),
            'target_blank' => (bool) $this->item['target_blank'],
            'visibility' => $this->item['visibility'],
            'is_active' => (bool) $this->item['is_active'],
        ];

        if ($type === MenuItemType::SystemPage || $type === MenuItemType::DynamicGroup) {
            $attributes['system_route'] = $this->item['system_route'] ?: null;
        }

        if (in_array($type, [MenuItemType::Page, MenuItemType::Model], true)) {
            [$linkType, $linkId] = explode(':', (string) $this->item['linkable']);
            $attributes['linkable_type'] = $linkType;
            $attributes['linkable_id'] = (int) $linkId;
        }

        if ($type === MenuItemType::External) {
            $attributes['url'] = trim((string) $this->item['url']);
        }

        if ($type === MenuItemType::DynamicGroup) {
            $attributes += [
                'dynamic_source' => $this->item['dynamic_source'],
                'dynamic_mode' => $this->item['dynamic_mode'],
                'dynamic_limit' => (int) $this->item['dynamic_limit'],
                'show_all_link' => (bool) $this->item['show_all_link'],
                'show_descriptions' => (bool) $this->item['show_descriptions'],
            ];
        }

        DB::transaction(function () use ($item, $attributes, $type): void {
            $item->update($attributes);

            if ($type === MenuItemType::DynamicGroup) {
                $item->entries()->delete();
                $entryType = MenuResolver::SOURCE_TYPES[$this->item['dynamic_source']] ?? 'service';

                foreach (array_values($this->item['entries']) as $sort => $id) {
                    $item->entries()->create(['entry_type' => $entryType, 'entry_id' => (int) $id, 'sort' => $sort + 1]);
                }
            }
        });

        unset($this->menu);
        $this->dispatch('form-saved');
        $this->toast('success', __('admin.menus.saved'));
    }

    public function saveMenu(): void
    {
        $menu = $this->menu;
        $this->authorize('update', $menu);
        $this->validate([...$this->pairRules('menuName', true, 80)], [], ['menuName.ar' => __('admin.menus.name')]);
        $menu->update(['name' => array_filter(Translate::pair($this->menuName))]);
        unset($this->menu);
        $this->toast('success', __('admin.menus.saved'));
    }

    public function toggleUseHeader(): void
    {
        $menu = $this->menu;
        $this->authorize('update', $menu);
        $menu->update(['use_header_menu' => ! $menu->use_header_menu]);
        unset($this->menu);
        $this->toast('success', __('admin.common.updated'));
    }

    /**
     * Label shown in the tree: custom label, else the target's title.
     */
    public function itemLabel(MenuItem $item): string
    {
        $custom = Translate::pick($item->getTranslations('label'), 'ar');

        if ($custom !== '') {
            return $custom;
        }

        return match ($item->type) {
            MenuItemType::Home => LinkOptions::routeLabel('home'),
            MenuItemType::SystemPage => LinkOptions::routeLabel((string) $item->system_route),
            MenuItemType::Page, MenuItemType::Model => (string) ($this->options['models'][$item->linkable_type]['items'][$item->linkable_id] ?? __('admin.menus.missing_target')),
            default => '—',
        };
    }

    public function itemDescription(MenuItem $item): string
    {
        if (in_array($item->type, [MenuItemType::Page, MenuItemType::Model], true)) {
            return (string) ($this->options['models'][$item->linkable_type]['label'] ?? '');
        }

        return MenuResolver::describe($item);
    }

    /**
     * Options of the manual-picks select for the current dynamic source.
     *
     * @return array<int, string>
     */
    public function entryOptions(): array
    {
        $type = MenuResolver::SOURCE_TYPES[$this->item['dynamic_source'] ?? 'services'] ?? 'service';

        return $this->options['models'][$type]['items'] ?? [];
    }

    public function render(): View
    {
        return view('livewire.admin.navigation.menus', [
            'locations' => MenuLocation::cases(),
        ])->title(__('admin.nav.menus'));
    }
}
