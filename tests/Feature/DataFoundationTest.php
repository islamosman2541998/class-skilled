<?php

use App\Enums\AssetKind;
use App\Enums\ProjectStatus;
use App\Models\Asset;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Services\AssetUploader;
use App\Services\SiteCache;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\MediaSettings;
use App\Support\LinkResolver;
use App\Support\LocalizedRoute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
});

function makeImageAsset(int $width = 800, int $height = 600): Asset
{
    return app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('photo.jpg', $width, $height));
}

test('slugs are generated per locale, keep arabic letters and create redirects on change', function () {
    $service = Service::create(['title' => ['ar' => 'أرضيات الإيبوكسي', 'en' => 'Epoxy flooring']]);

    expect($service->getTranslation('slug', 'ar'))->toBe('أرضيات-الإيبوكسي')
        ->and($service->getTranslation('slug', 'en'))->toBe('epoxy-flooring');

    $second = Service::create(['title' => ['ar' => 'أرضيات الإيبوكسي']]);
    expect($second->getTranslation('slug', 'ar'))->toBe('أرضيات-الإيبوكسي-1')
        ->and($second->hasLocale('en'))->toBeFalse();

    $service->setTranslation('slug', 'en', 'epoxy-floors')->save();

    $redirect = Redirect::where('from_path', '/en/services/epoxy-flooring')->first();
    expect($redirect)->not->toBeNull()
        ->and($redirect->to_path)->toBe('/en/services/epoxy-floors')
        ->and($redirect->is_auto)->toBeTrue();

    // Changing it back must not leave a loop or a chain.
    $service->setTranslation('slug', 'en', 'epoxy-flooring')->save();
    expect(Redirect::where('from_path', '/en/services/epoxy-flooring')->exists())->toBeFalse()
        ->and(Redirect::where('from_path', '/en/services/epoxy-floors')->value('to_path'))->toBe('/en/services/epoxy-flooring');

    $service->setTranslation('slug', 'ar', 'إيبوكسي')->save();
    expect(Redirect::where('from_path', '/الخدمات/أرضيات-الإيبوكسي')->value('to_path'))->toBe('/الخدمات/إيبوكسي');
});

test('publishable scopes handle drafts, published and scheduled rows', function () {
    $published = Post::create(['title' => ['ar' => 'منشور'], 'status' => 'published', 'published_at' => now()->subDay()]);
    $scheduled = Post::create(['title' => ['ar' => 'مجدول'], 'status' => 'published', 'published_at' => now()->addDay()]);
    $draft = Post::create(['title' => ['ar' => 'مسودة'], 'status' => 'draft']);

    expect(Post::published()->pluck('id')->all())->toBe([$published->id])
        ->and(Post::scheduled()->pluck('id')->all())->toBe([$scheduled->id])
        ->and(Post::draft()->pluck('id')->all())->toBe([$draft->id])
        ->and($scheduled->is_scheduled)->toBeTrue()
        ->and($scheduled->displayStatus()->value)->toBe('scheduled');

    $this->travel(2)->days();
    expect(Post::published()->count())->toBe(2);
});

test('assets keep their order inside a collection', function () {
    $a = makeImageAsset();
    $b = makeImageAsset();
    $c = makeImageAsset();
    $service = Service::create(['title' => ['ar' => 'خدمة']]);

    $service->syncAssets('gallery', [$c->id, $a->id, $b->id]);
    expect($service->assetsIn('gallery')->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);

    $service->syncAssets('gallery', [$b->id, $c->id]);
    $service->attachAsset($a, 'cover');

    $loaded = Service::with('assets.media')->find($service->id);
    expect($loaded->assetsIn('gallery')->pluck('id')->all())->toBe([$b->id, $c->id])
        ->and($loaded->firstAsset('cover')?->id)->toBe($a->id)
        ->and($a->usages())->toHaveCount(1);
});

test('the uploader downscales images and generates webp conversions', function () {
    $asset = makeImageAsset(3200, 1600);
    $max = app(MediaSettings::class)->max_image_dimension;

    expect($asset->kind)->toBe(AssetKind::Image)
        ->and($asset->width)->toBe($max)
        ->and($asset->height)->toBe((int) ($max / 2));

    $media = $asset->file();
    foreach (['thumb', 'sm', 'md', 'lg', 'xl'] as $conversion) {
        expect($media->hasGeneratedConversion($conversion))->toBeTrue();
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot($conversion));
        expect($media->getPathRelativeToRoot($conversion))->toEndWith('.webp');
    }
});

test('the uploader rejects unsupported files and parses embeds', function () {
    expect(fn () => app(AssetUploader::class)->upload(UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload')))
        ->toThrow(ValidationException::class);

    expect(AssetUploader::parseEmbedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')['id'])->toBe('dQw4w9WgXcQ')
        ->and(AssetUploader::parseEmbedUrl('https://youtu.be/dQw4w9WgXcQ')['provider']->value)->toBe('youtube')
        ->and(AssetUploader::parseEmbedUrl('https://vimeo.com/123456789')['id'])->toBe('123456789')
        ->and(AssetUploader::parseEmbedUrl('https://example.com/video'))->toBeNull();
});

test('the link resolver handles every link type', function () {
    $page = Page::create(['title' => ['ar' => 'من نحن', 'en' => 'About us'], 'status' => 'published']);
    $draft = Page::create(['title' => ['ar' => 'مسودة'], 'status' => 'draft']);
    $inactive = Service::create(['title' => ['ar' => 'غير نشطة'], 'is_active' => false]);

    expect(LinkResolver::url(['type' => 'system_page', 'route' => 'contact'], 'en'))->toBe(url('/en/contact'))
        ->and(LinkResolver::url(['type' => 'system_page', 'route' => 'quote', 'query' => ['service' => 3]], 'ar'))->toBe(url('/طلب-عرض-سعر').'?service=3')
        ->and(LinkResolver::url(['type' => 'system_page', 'route' => 'home'], 'ar'))->toBe(url('/'))
        ->and(LinkResolver::url(['type' => 'system_page', 'route' => 'admin.dashboard'], 'ar'))->toBeNull()
        ->and(LinkResolver::url(['type' => 'page', 'model_type' => 'page', 'model_id' => $page->id], 'en'))->toBe(url('/en/about-us'))
        ->and(LinkResolver::url(['type' => 'page', 'model_type' => 'page', 'model_id' => $draft->id], 'ar'))->toBeNull()
        ->and(LinkResolver::url(['type' => 'model', 'model_type' => 'service', 'model_id' => $inactive->id], 'ar'))->toBeNull()
        ->and(LinkResolver::url(['type' => 'model', 'model_type' => 'service', 'model_id' => 999], 'ar'))->toBeNull()
        ->and(LinkResolver::url(['type' => 'external', 'url' => 'https://example.com'], 'ar'))->toBe('https://example.com')
        ->and(LinkResolver::url(['type' => 'external', 'url' => 'javascript:alert(1)'], 'ar'))->toBeNull()
        ->and(LinkResolver::url(['type' => 'anchor', 'anchor' => 'contact'], 'ar'))->toBe('#contact')
        ->and(LinkResolver::url(['type' => 'whatsapp'], 'ar'))->toStartWith('https://wa.me/971527649086')
        ->and(LinkResolver::url(['type' => 'phone'], 'ar'))->toBe('tel:+971589820017')
        ->and(LinkResolver::url(null))->toBeNull();
});

test('saving a content model flushes the site cache', function () {
    $cache = app(SiteCache::class);
    $cache->remember('probe', fn () => 'old');
    expect($cache->remember('probe', fn () => 'new'))->toBe('old');

    Service::create(['title' => ['ar' => 'خدمة جديدة']]);

    expect($cache->remember('probe', fn () => 'new'))->toBe('new');
});

test('settings defaults are loaded from the settings migration', function () {
    expect(app(CompanySettings::class)->trade_licence_no)->toBe('CN-5898460')
        ->and(app(CompanySettings::class)->classification_expiry)->toBe('2028-03-25')
        ->and(app(ContactSettings::class)->phones)->toContain('+971 58 982 0017');
});

test('localized paths are decoded and normalised', function () {
    expect(LocalizedRoute::path('services.show', 'ar', ['slug' => 'عزل']))->toBe('/الخدمات/عزل')
        ->and(LocalizedRoute::normalizePath('https://x.test/%D8%A8%D8%AD%D8%AB/?q=1'))->toBe('/بحث');
});

test('essential seeder creates roles, statuses, menus and home sections', function () {
    expect(Role::pluck('name')->sort()->values()->all())->toBe(['admin', 'editor', 'sales'])
        ->and(RequestStatus::count())->toBe(16)
        ->and(Menu::count())->toBe(7)
        ->and(HomeSection::count())->toBe(11)
        ->and(__('site.request_quote', [], 'en'))->toBe('Request a quote')
        ->and(ProjectStatus::Ongoing->label())->toBe('قيد التنفيذ');
});
