<?php

use App\Livewire\Admin\Media\Library;
use App\Livewire\Admin\Seo\Redirects;
use App\Livewire\Admin\Settings\Appearance;
use App\Livewire\Admin\Settings\Contact;
use App\Livewire\Admin\Settings\UiStrings;
use App\Livewire\Admin\System\Backups;
use App\Models\Asset;
use App\Models\Redirect;
use App\Models\Service;
use App\Services\AssetUploader;
use App\Services\BackupManager;
use App\Services\SiteCache;
use App\Settings\ContactSettings;
use App\Settings\MaintenanceSettings;
use App\Settings\SeoSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\TranslationLoader\LanguageLine;

beforeEach(function () {
    Storage::fake('public');
});

test('the media library uploads and processes images', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(Library::class)
        ->set('upload', UploadedFile::fake()->image('site-photo.jpg', 1800, 1200))
        ->call('storeUpload', 'upload', ['kind' => 'image'])
        ->assertHasNoErrors();

    $asset = Asset::query()->latest('id')->first();
    expect($asset->original_name)->toBe('site-photo.jpg')
        ->and($asset->file()->hasGeneratedConversion('md'))->toBeTrue();
});

test('assets in use cannot be deleted except forced by an admin, and replace keeps usages', function () {
    $asset = app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('a.jpg', 800, 600));
    $service = Service::create(['title' => ['ar' => 'خدمة']]);
    $service->syncAssets('cover', [$asset->id]);

    $this->actingAs(userWithRole('editor'));
    Livewire::test(Library::class)->call('open', $asset->id)->call('delete', $asset->id);
    expect(Asset::find($asset->id))->not->toBeNull();

    app(AssetUploader::class)->replace($asset, UploadedFile::fake()->image('b.png', 600, 400));
    expect($asset->fresh()->original_name)->toBe('b.png')
        ->and($service->fresh()->firstAsset('cover')?->id)->toBe($asset->id);

    $this->actingAs(userWithRole('admin'));
    Livewire::test(Library::class)->call('delete', $asset->id, true);
    expect(Asset::withTrashed()->find($asset->id))->toBeNull();
});

test('asset details save alt text and focal point', function () {
    $asset = app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('a.jpg', 800, 600));
    $this->actingAs(userWithRole('editor'));

    Livewire::test(Library::class)
        ->call('open', $asset->id)
        ->set('details.alt.ar', 'نص بديل')
        ->set('details.alt.en', 'Alt text')
        ->set('details.focal', 'top')
        ->call('saveDetails')
        ->assertHasNoErrors();

    expect($asset->fresh()->altText('en'))->toBe('Alt text')->and($asset->fresh()->focal)->toBe('top');
});

test('settings are saved, cached values refreshed and the site cache flushed', function () {
    $this->actingAs(userWithRole('admin'));
    $cache = app(SiteCache::class);
    $cache->remember('probe', fn () => 'old');

    Livewire::test(Contact::class)
        ->set('data.phones.0.value', '+971 50 111 2222')
        ->set('data.whatsapp_number', '+971 50 111 2222')
        ->call('save')
        ->assertHasNoErrors();

    expect(app(ContactSettings::class)->phones[0])->toBe('+971 50 111 2222')
        ->and($cache->remember('probe', fn () => 'new'))->toBe('new');
});

test('settings screens are protected by permissions', function () {
    $this->actingAs(userWithRole('sales'))->get(route('admin.settings.general'))->assertForbidden();
    $this->actingAs(userWithRole('editor'))->get(route('admin.settings.general'))->assertForbidden();
    $this->actingAs(userWithRole('editor'))->get(route('admin.appearance'))->assertOk();
    $this->actingAs(userWithRole('editor'))->get(route('admin.settings.tracking'))->assertForbidden();

    $admin = userWithRole('admin');
    foreach (['general', 'company', 'contact', 'social', 'notifications', 'forms', 'media', 'tracking', 'maintenance', 'system'] as $group) {
        $this->actingAs($admin)->get(route('admin.settings.'.$group))->assertOk();
    }
    foreach (['admin.media.index', 'admin.seo.settings', 'admin.seo.health', 'admin.redirects.index', 'admin.ui-strings.index', 'admin.backups.index'] as $route) {
        $this->actingAs($admin)->get(route($route))->assertOk();
    }
});

test('appearance changes are printed as CSS variables in the site layout', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(Appearance::class)
        ->set('data.colors.navy', '#112233')
        ->set('data.heading_font', 'tajawal')
        ->call('save')
        ->assertHasNoErrors();

    auth()->logout();
    $this->get('/')->assertOk()
        ->assertSee('--c-navy:#112233', false)
        ->assertSee("--font-head:'Tajawal', sans-serif", false);
});

test('ui strings can be edited and reset to default', function () {
    $this->actingAs(userWithRole('editor'));
    $line = LanguageLine::query()->where('group', 'site')->where('key', 'request_quote')->firstOrFail();

    Livewire::test(UiStrings::class)
        ->call('edit', $line->id)
        ->set('text.ar', 'اطلب تسعيرة')
        ->call('save')
        ->assertHasNoErrors();
    app('translator')->setLoaded([]);
    expect(__('site.request_quote', [], 'ar'))->toBe('اطلب تسعيرة');

    Livewire::test(UiStrings::class)->call('resetDefault', $line->id);
    app('translator')->setLoaded([]);
    expect(__('site.request_quote', [], 'ar'))->toBe('اطلب عرض سعر');
});

test('redirects apply before routing, including arabic paths', function () {
    Redirect::create(['from_path' => '/قديم', 'to_path' => '/en/contact', 'status_code' => 301]);

    $this->get('/'.rawurlencode('قديم').'?x=1')->assertRedirect(url('/en/contact').'?x=1')->assertStatus(301);
    expect(Redirect::first()->hits)->toBe(1);

    Redirect::create(['from_path' => '/en/old', 'to_path' => '/الخدمات', 'status_code' => 302]);
    $this->get('/en/old')->assertStatus(302)->assertRedirect(url('/'.rawurlencode('الخدمات')));
});

test('redirect loops are rejected', function () {
    $this->actingAs(userWithRole('editor'));
    Redirect::create(['from_path' => '/a', 'to_path' => '/b']);

    Livewire::test(Redirects::class)
        ->set('form.from_path', '/b')
        ->set('form.to_path', '/a')
        ->call('save')
        ->assertHasErrors('form.to_path');
});

test('robots.txt is dynamic and lists the sitemap', function () {
    $this->app->detectEnvironment(fn () => 'production');
    $settings = app(SeoSettings::class);
    $settings->robots_txt = "User-agent: *\nDisallow: /private";
    $settings->save();

    $this->get('/robots.txt')->assertOk()
        ->assertSee('Disallow: /private', false)
        ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
});

test('maintenance mode hides the site from guests but not from users or allowed ips', function () {
    $settings = app(MaintenanceSettings::class);
    $settings->enabled = true;
    $settings->allowed_ips = ['10.0.0.9'];
    $settings->save();

    $this->get('/')->assertStatus(503)->assertSee(__('errors.maintenance_title'));
    $this->get('/', ['REMOTE_ADDR' => '10.0.0.9'])->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])->get('/')->assertOk();
    $this->actingAs(userWithRole('editor'))->get('/')->assertOk();
});

test('a backup can be created and downloaded through a signed url', function () {
    $admin = userWithRole('admin');
    $this->actingAs($admin);

    Livewire::test(Backups::class)->call('create')->assertDispatched('toast', type: 'success');

    $name = app(BackupManager::class)->list()[0]['name'] ?? null;
    expect($name)->not->toBeNull();

    $this->get(URL::temporarySignedRoute('admin.backups.download', now()->addMinute(), ['name' => $name]))->assertOk();
    $this->get(route('admin.backups.download', ['name' => $name]))->assertForbidden();

    app(BackupManager::class)->delete($name);
});
