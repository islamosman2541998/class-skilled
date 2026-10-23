<?php

use App\Http\Controllers\PlaceholderController;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Infrastructure endpoints stay outside the localized route group.
Route::post('/t', PlaceholderController::class)->name('track');
Route::get('/sitemap.xml', PlaceholderController::class)->name('sitemap');

Route::prefix(config('admin.path'))
    ->middleware(SetAdminLocale::class)
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', PlaceholderController::class)->name('dashboard');
    });

Route::prefix(LaravelLocalization::setLocale())
    ->middleware(['localize', 'localizationRedirect'])
    ->group(function (): void {
        Route::get('/', PlaceholderController::class)->name('home');

        foreach ([
            'services.index', 'services.show',
            'projects.index', 'projects.category', 'projects.show',
            'posts.index', 'posts.category', 'posts.show',
            'careers.index', 'careers.show',
            'quote', 'contact', 'faq', 'search',
        ] as $name) {
            Route::get(LaravelLocalization::transRoute('routes.'.$name), PlaceholderController::class)
                ->name($name);
        }

        // Custom pages must remain the last public route.
        Route::get(LaravelLocalization::transRoute('routes.pages.show'), PlaceholderController::class)
            ->name('pages.show');
    });
