<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Laravel\Fortify\Fortify;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;

class AppServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Custom authentication screens and actions belong to phase 02.
        Fortify::ignoreRoutes();
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(new Driver));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RouteServiceProvider::loadCachedRoutesUsing(fn () => $this->loadCachedRoutes());
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
