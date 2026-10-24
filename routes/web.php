<?php

use App\Http\Controllers\Site\CareerController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\PublicStorageController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\TrackController;
use App\Http\Middleware\CaptureFirstTouch;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\SetAdminLocale;
use App\Services\FaviconGenerator;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\ResponseCache\Middlewares\CacheResponse;

// Infrastructure endpoints stay outside the localized route group.
Route::post('/t', TrackController::class)->middleware('throttle:60,1')->name('track');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/sitemaps/{type}.xml', SitemapController::class)->where('type', '[a-z-]+')->name('sitemap.type');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/favicon.ico', function (FaviconGenerator $favicons) {
    $path = $favicons->icoPath();

    return $path
        ? response()->file($path, ['Content-Type' => 'image/x-icon', 'Cache-Control' => 'public, max-age=86400'])
        : response()->file(public_path('images/brand/mark-sm.png'), ['Content-Type' => 'image/png']);
})->name('favicon');

// Hosts without symlink support: serve the public disk at /storage (see DEPLOY.md).
if (! file_exists(public_path('storage'))) {
    Route::get('/storage/{path}', PublicStorageController::class)->where('path', '.*')->name('storage.fallback');
}

Route::prefix(config('admin.path'))
    ->middleware(SetAdminLocale::class)
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

Route::prefix(LaravelLocalization::setLocale())
    ->middleware(['localize', 'localizationRedirect', MaintenanceMode::class, CaptureFirstTouch::class, CacheResponse::class])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');

        $routes = [
            'services.index' => [ServiceController::class, 'index'],
            'services.show' => [ServiceController::class, 'show'],
            'projects.index' => [ProjectController::class, 'index'],
            'projects.category' => [ProjectController::class, 'category'],
            'projects.show' => [ProjectController::class, 'show'],
            'posts.index' => [PostController::class, 'index'],
            'posts.category' => [PostController::class, 'category'],
            'posts.show' => [PostController::class, 'show'],
            'careers.index' => [CareerController::class, 'index'],
            'careers.show' => [CareerController::class, 'show'],
            'quote' => [PageController::class, 'quote'],
            'contact' => [PageController::class, 'contact'],
            'faq' => [PageController::class, 'faq'],
            'search' => SearchController::class,
        ];

        foreach ($routes as $name => $action) {
            Route::get(LaravelLocalization::transRoute('routes.'.$name), $action)->name($name);
        }

        // Custom pages must remain the last public route.
        Route::get(LaravelLocalization::transRoute('routes.pages.show'), [PageController::class, 'show'])
            ->name('pages.show');
    });
