<?php

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Livewire\Admin\Navigation\Menus;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Service;
use App\Services\Menus\LinkIndex;
use App\Services\Menus\MenuResolver;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function headerMenu(): Menu
{
    $menu = Menu::where('location', 'header')->firstOrFail();
    $menu->items()->delete();

    return $menu;
}

function menuNodes(string $location, string $locale): array
{
    return app(MenuResolver::class)->forLocation(MenuLocation::from($location), $locale, false);
}

test('labels inherit from the linked page per locale and hidden targets are skipped', function () {
    $menu = headerMenu();
    $about = Page::create(['title' => ['ar' => 'من نحن', 'en' => 'About'], 'status' => 'published']);
    $arabicOnly = Page::create(['title' => ['ar' => 'صفحة عربية'], 'status' => 'published']);
    $draft = Page::create(['title' => ['ar' => 'مسودة', 'en' => 'Draft'], 'status' => 'draft']);

    foreach ([$about, $arabicOnly, $draft] as $i => $page) {
        $menu->items()->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $page->id, 'sort' => $i + 1]);
    }
    $menu->items()->create(['type' => MenuItemType::SystemPage, 'system_route' => 'contact', 'label' => ['ar' => 'اتصل', 'en' => 'Call us'], 'sort' => 9]);

    $ar = collect(menuNodes('header', 'ar'))->pluck('label')->all();
    $en = collect(menuNodes('header', 'en'))->pluck('label')->all();

    expect($ar)->toBe(['من نحن', 'صفحة عربية', 'اتصل'])
        ->and($en)->toBe(['About', 'Call us'])
        ->and(menuNodes('header', 'en')[0]->url)->toBe(url('/en/about'));
});

test('dynamic groups support manual order, featured, latest and limits', function () {
    $menu = headerMenu();
    $a = Service::create(['title' => ['ar' => 'أ', 'en' => 'A'], 'summary' => ['ar' => 'وصف أ'], 'sort' => 1]);
    $b = Service::create(['title' => ['ar' => 'ب', 'en' => 'B'], 'is_featured' => true, 'sort' => 2]);
    $c = Service::create(['title' => ['ar' => 'ج', 'en' => 'C'], 'sort' => 3]);
    Service::create(['title' => ['ar' => 'د', 'en' => 'D'], 'is_active' => false, 'sort' => 4]);

    $group = $menu->items()->create([
        'type' => MenuItemType::DynamicGroup, 'system_route' => 'services.index', 'label' => ['ar' => 'الخدمات', 'en' => 'Services'],
        'dynamic_source' => DynamicSource::Services, 'dynamic_mode' => DataSource::Manual, 'dynamic_limit' => 8, 'show_all_link' => true,
    ]);
    foreach ([$c, $a] as $i => $service) {
        $group->entries()->create(['entry_type' => 'service', 'entry_id' => $service->id, 'sort' => $i + 1]);
    }

    $node = menuNodes('header', 'ar')[0];
    expect(collect($node->children)->pluck('label')->all())->toBe(['ج', 'أ'])
        ->and($node->children[1]->description)->toBe('وصف أ')
        ->and($node->viewAll['url'])->toBe(url('/'.'الخدمات'));

    $group->update(['dynamic_mode' => DataSource::Featured]);
    expect(collect(menuNodes('header', 'ar')[0]->children)->pluck('label')->all())->toBe(['ب']);

    $group->update(['dynamic_mode' => DataSource::All, 'dynamic_limit' => 2]);
    expect(collect(menuNodes('header', 'en')[0]->children)->pluck('label')->all())->toBe(['A', 'B']);

    $group->update(['dynamic_mode' => DataSource::Latest, 'dynamic_limit' => 8]);
    expect(collect(menuNodes('header', 'ar')[0]->children)->pluck('label')->all())->toBe(['ج', 'ب', 'أ']);
});

test('menus are cached and refreshed when linked content changes', function () {
    $menu = headerMenu();
    $page = Page::create(['title' => ['ar' => 'قديم'], 'status' => 'published']);
    $menu->items()->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $page->id]);

    expect(menuNodes('header', 'ar')[0]->label)->toBe('قديم');
    $page->update(['title' => ['ar' => 'جديد']]);
    expect(menuNodes('header', 'ar')[0]->label)->toBe('جديد');
});

test('a menu builds with at most 3 queries once the link index is warm', function () {
    $menu = headerMenu();
    $page = Page::create(['title' => ['ar' => 'صفحة'], 'status' => 'published']);
    $service = Service::create(['title' => ['ar' => 'خدمة']]);
    $menu->items()->create(['type' => MenuItemType::Home]);
    $menu->items()->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $page->id]);
    $group = $menu->items()->create(['type' => MenuItemType::DynamicGroup, 'system_route' => 'services.index', 'dynamic_source' => DynamicSource::Services, 'dynamic_mode' => DataSource::Manual]);
    $group->entries()->create(['entry_type' => 'service', 'entry_id' => $service->id, 'sort' => 1]);

    foreach (array_keys(LinkIndex::TYPES) as $type) {
        app(LinkIndex::class)->of($type);
    }

    __('nav.home');
    __('site.view_all');
    DB::flushQueryLog();
    DB::enableQueryLog();
    $nodes = app(MenuResolver::class)->build(MenuLocation::Header, 'ar');
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($nodes)->toHaveCount(3)->and($queries)->toBeLessThanOrEqual(3);
});

test('active state follows the current url including children', function () {
    $menu = headerMenu();
    $service = Service::create(['title' => ['ar' => 'عزل']]);
    $menu->items()->create(['type' => MenuItemType::Home, 'sort' => 1]);
    $menu->items()->create(['type' => MenuItemType::DynamicGroup, 'system_route' => 'services.index', 'dynamic_source' => DynamicSource::Services, 'dynamic_mode' => DataSource::All, 'sort' => 2]);

    $this->get('/'.rawurlencode('الخدمات').'/'.rawurlencode('عزل'));
    $nodes = app(MenuResolver::class)->forLocation(MenuLocation::Header, 'ar');

    expect($nodes[0]->active)->toBeFalse()->and($nodes[1]->active)->toBeTrue()->and($nodes[1]->children[0]->active)->toBeTrue();
});

test('the mobile menu mirrors the header by default', function () {
    $menu = headerMenu();
    $menu->items()->create(['type' => MenuItemType::SystemPage, 'system_route' => 'faq']);

    expect(collect(menuNodes('mobile', 'ar'))->pluck('label')->all())->toBe([__('nav.faq', [], 'ar')]);
});

test('the builder adds items, nests them (depth 2) and saves dynamic picks', function () {
    $this->actingAs(userWithRole('editor'));
    headerMenu();
    $services = collect([Service::create(['title' => ['ar' => 'س1']]), Service::create(['title' => ['ar' => 'س2']])]);

    $component = Livewire::test(Menus::class)
        ->call('addSystem', 'contact')
        ->call('addSystem', 'faq')
        ->call('addDynamic');

    [$contact, $faq, $group] = MenuItem::query()->whereHas('menu', fn ($q) => $q->where('location', 'header'))->orderBy('id')->get()->all();

    $component->call('saveTree', [
        ['id' => $contact->id, 'parent' => null, 'sort' => 1],
        ['id' => $faq->id, 'parent' => $contact->id, 'sort' => 1],
        ['id' => $group->id, 'parent' => null, 'sort' => 2],
    ]);
    expect($faq->fresh()->parent_id)->toBe($contact->id);

    // depth 3 is refused
    $component->call('saveTree', [
        ['id' => $contact->id, 'parent' => null, 'sort' => 1],
        ['id' => $faq->id, 'parent' => $contact->id, 'sort' => 1],
        ['id' => $group->id, 'parent' => $faq->id, 'sort' => 1],
    ]);
    expect($group->fresh()->parent_id)->toBeNull();

    $component->call('edit', $group->id)
        ->set('item.dynamic_mode', 'manual')
        ->set('item.entries', [$services[1]->id, $services[0]->id])
        ->call('saveItem')
        ->assertHasNoErrors();

    expect($group->fresh()->entries()->pluck('entry_id')->all())->toBe([$services[1]->id, $services[0]->id]);
});

test('editors may manage menus, sales may not', function () {
    $this->actingAs(userWithRole('editor'))->get(route('admin.menus.index'))->assertOk();
    $this->actingAs(userWithRole('sales'))->get(route('admin.menus.index'))->assertForbidden();
});
