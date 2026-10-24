<?php

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\SectionType;
use App\Livewire\Admin\Content\HomeSections;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Service;
use App\Services\AssetUploader;
use App\Services\SeoManager;
use App\Support\LocalizedRoute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function homeSection(SectionType $type): HomeSection
{
    return HomeSection::where('type', $type->value)->firstOrFail();
}

test('home renders in arabic with layout parts driven by settings and sections', function () {
    $whyUs = homeSection(SectionType::WhyUs);

    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', false)
        ->assertSee($whyUs->getTranslation('title', 'ar'))
        ->assertSee(__('site.request_quote'))
        ->assertSee('data-header', false)
        ->assertSee('data-mobile-nav', false)
        ->assertSee('data-bottom-bar', false)
        ->assertSee('CN-5898460')
        ->assertSee('href="'.url('/en').'"', false);
});

test('home renders in english', function () {
    $this->refreshApplicationWithLocale('en');
    $whyUs = homeSection(SectionType::WhyUs);

    $this->get('/en')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr"', false)
        ->assertSee($whyUs->getTranslation('title', 'en'))
        ->assertSee('Class Skilled');
});

test('hidden sections are not rendered and edits show immediately', function () {
    $whyUs = homeSection(SectionType::WhyUs);
    $title = $whyUs->getTranslation('title', 'ar');

    $this->get('/')->assertSee($title);

    $whyUs->update(['is_visible' => false]);
    $this->get('/')->assertDontSee($title)->assertDontSee('data-section="why_us"', false);

    $whyUs->update(['is_visible' => true, 'title' => ['ar' => 'عنوان جديد للقسم', 'en' => 'New title']]);
    $this->get('/')->assertSee('عنوان جديد للقسم');
});

test('swiper markup is present only when a slider section has items', function () {
    $services = homeSection(SectionType::Services);
    $services->update(['source' => DataSource::Latest]);
    Service::query()->update(['is_active' => false]);

    $this->get('/')->assertDontSee('data-section="services"', false);

    Service::create(['title' => ['ar' => 'خدمة العزل', 'en' => 'Insulation'], 'excerpt' => ['ar' => 'وصف'], 'is_active' => true]);

    $this->get('/')
        ->assertSee('data-section="services"', false)
        ->assertSee('cardSlider', false)
        ->assertSee('خدمة العزل');
});

test('header dropdown children come from the menu resolver', function () {
    $menu = Menu::where('location', 'header')->firstOrFail();
    $menu->items()->delete();
    Service::create(['title' => ['ar' => 'أرضيات إيبوكسي', 'en' => 'Epoxy floors'], 'summary' => ['ar' => 'وصف قصير'], 'is_active' => true]);

    $menu->items()->create([
        'type' => MenuItemType::DynamicGroup, 'system_route' => 'services.index', 'label' => ['ar' => 'الخدمات', 'en' => 'Services'],
        'dynamic_source' => DynamicSource::Services, 'dynamic_mode' => DataSource::All, 'dynamic_limit' => 8, 'show_all_link' => true,
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('class="dropdown-panel"')
        ->and($html)->toContain('أرضيات إيبوكسي')
        ->and($html)->toContain('x-data="navDropdown"');
});

test('language switch links to the same page in the other locale', function () {
    $this->get(LocalizedRoute::url('services.index', 'ar'))
        ->assertOk()
        ->assertSee('href="'.LocalizedRoute::url('services.index', 'en').'"', false);

    // Pages with translated slugs set their alternates; untranslated pages fall back to home.
    app(SeoManager::class)->alternates(['ar' => url('/خدمة'), 'en' => null]);
    expect(app(SeoManager::class)->alternateUrl('en'))->toBe(url('/en'))
        ->and(app(SeoManager::class)->alternateUrl('ar'))->toBe(url('/خدمة'));
});

test('unknown urls render the styled 404 page', function () {
    $this->get('/a/b/c/d')
        ->assertNotFound()
        ->assertSee(__('errors.404_title'))
        ->assertSee('role="search"', false);
});

test('every home section view exists', function () {
    foreach (SectionType::cases() as $type) {
        expect(view()->exists('site.sections.'.str_replace('_', '-', $type->value)))->toBeTrue($type->value);
    }
});

test('the about section has a title, text and one image, and there is no facts section', function () {
    Storage::fake('public');
    $image = app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('about.jpg', 1200, 900));
    $about = homeSection(SectionType::About);
    $this->actingAs(userWithRole('editor'));

    Livewire\Livewire::test(HomeSections::class)
        ->call('edit', $about->id)
        ->set('section.data.text', ['ar' => 'نص تعريفي كامل عن الشركة', 'en' => 'Full company text'])
        ->set('section.data.image_asset_id', $image->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($about->fresh()->data)->not->toHaveKey('meta')->not->toHaveKey('image_asset_ids');
    expect(collect(SectionType::cases())->map->value->all())->not->toContain('facts');

    auth()->logout();
    $html = $this->get('/')->assertOk()->getContent();
    expect($html)->toContain('نص تعريفي كامل عن الشركة')
        ->and(substr_count($html, 'data-reveal="image"'))->toBeGreaterThanOrEqual(1)
        ->and($html)->not->toContain('data-section="facts"');
});
