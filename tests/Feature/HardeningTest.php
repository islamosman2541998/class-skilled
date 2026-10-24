<?php

use App\Http\CacheProfiles\SiteCacheProfile;
use App\Http\Controllers\Site\PublicStorageController;
use App\Livewire\Admin\System\Health;
use App\Livewire\Site\QuoteForm;
use App\Models\Service;
use App\Support\LocalizedRoute;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('guest pages are served from the full-page cache and refreshed on content changes', function () {
    config(['responsecache.enabled' => true]);
    $service = Service::create(['title' => ['ar' => 'خدمة أولى', 'en' => 'First']]);
    $url = LocalizedRoute::url('services.index', 'ar');

    $this->get($url)->assertOk()->assertSee('خدمة أولى');

    // A change that bypasses model events is not visible: the cached page is served.
    DB::table('services')->where('id', $service->id)->update(['title' => json_encode(['ar' => 'اسم معدل مباشرة', 'en' => 'Raw'])]);
    $this->get($url)->assertSee('خدمة أولى')->assertDontSee('اسم معدل مباشرة');

    // A dashboard save fires the observers, which flush the cache.
    $service->refresh()->update(['title' => ['ar' => 'اسم جديد', 'en' => 'New']]);
    $this->get($url)->assertSee('اسم جديد');
});

test('the cache is bypassed for users, query strings and form pages', function () {
    $profile = new SiteCacheProfile;
    $make = fn (string $uri) => tap(Request::create($uri), function ($request) {
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
    });

    expect($profile->shouldCacheRequest($make(LocalizedRoute::url('services.index', 'ar'))))->toBeTrue()
        ->and($profile->shouldCacheRequest($make(LocalizedRoute::url('services.index', 'ar').'?page=2')))->toBeFalse()
        ->and($profile->shouldCacheRequest($make(LocalizedRoute::url('quote', 'ar'))))->toBeFalse()
        ->and($profile->shouldCacheRequest($make(LocalizedRoute::url('search', 'ar'))))->toBeFalse();

    $authenticated = $make(LocalizedRoute::url('services.index', 'ar'));
    $authenticated->setUserResolver(fn () => userWithRole('editor'));
    expect($profile->shouldCacheRequest($authenticated))->toBeFalse();
});

test('security headers are sent on site and dashboard responses', function () {
    // Independent of a local `npm run dev` (public/hot): test the built-assets behaviour.
    Vite::useHotFile(storage_path('framework/testing-no-hot'));
    config(['responsecache.enabled' => false]);

    $response = $this->get('/')->assertOk();

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")->toContain("object-src 'none'");

    $this->get(route('login'))->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

test('uploads are checked by content, not only by extension', function () {
    Storage::fake('local');

    Livewire::test(QuoteForm::class)
        ->set('newFiles', [UploadedFile::fake()->create('drawing.pdf', 50, 'text/x-php')])
        ->assertHasErrors('newFiles.0')
        ->assertCount('files', 0);
});

test('site:check reports the environment and the health page shows it to admins only', function () {
    $this->artisan('site:check')->expectsOutputToContain('ext-gd')->assertSuccessful();

    $this->actingAs(userWithRole('admin'));
    Livewire::test(Health::class)->assertOk()->assertSee('ext-fileinfo')->assertSee(__('admin.health.groups.php'));

    $this->actingAs(userWithRole('sales'));
    Livewire::test(Health::class)->assertForbidden();
});

test('the public storage fallback serves media but never scripts or traversal', function () {
    Storage::fake('public');
    Storage::disk('public')->put('demo/photo.jpg', 'jpg-bytes');
    Storage::disk('public')->put('demo/shell.php', '<?php echo 1;');

    $controller = new PublicStorageController;

    expect($controller('demo/photo.jpg')->headers->get('Content-Type'))->toBe('image/jpeg');
    expect(fn () => $controller('demo/shell.php'))->toThrow(NotFoundHttpException::class);
    expect(fn () => $controller('../.env'))->toThrow(NotFoundHttpException::class);
});
