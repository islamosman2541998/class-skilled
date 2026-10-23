<?php

use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\Site\CareerController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Infrastructure endpoints stay outside the localized route group.
Route::post('/t', PlaceholderController::class)->name('track');
Route::get('/sitemap.xml', PlaceholderController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::prefix(config('admin.path'))
    ->middleware(SetAdminLocale::class)
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

Route::prefix(LaravelLocalization::setLocale())
    ->middleware(['localize', 'localizationRedirect', MaintenanceMode::class])
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
            'quote' => PlaceholderController::class,
            'contact' => PlaceholderController::class,
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
