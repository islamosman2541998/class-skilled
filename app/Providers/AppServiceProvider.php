<?php

namespace App\Providers;

use App\Models;
use App\Services\SiteCache;
use App\Services\Sitemap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Laravel\Fortify\Fortify;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\TranslationLoader\LanguageLine;

class AppServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * Short, stable names stored in polymorphic columns.
     *
     * @var array<string, class-string<Model>>
     */
    public const MORPH_MAP = [
        'user' => Models\User::class,
        'asset' => Models\Asset::class,
        'media_folder' => Models\MediaFolder::class,
        'page' => Models\Page::class,
        'service' => Models\Service::class,
        'project' => Models\Project::class,
        'project_category' => Models\ProjectCategory::class,
        'post' => Models\Post::class,
        'post_category' => Models\PostCategory::class,
        'faq' => Models\Faq::class,
        'testimonial' => Models\Testimonial::class,
        'client' => Models\Client::class,
        'team_member' => Models\TeamMember::class,
        'certificate' => Models\Certificate::class,
        'job_post' => Models\JobPost::class,
        'home_section' => Models\HomeSection::class,
        'menu' => Models\Menu::class,
        'menu_item' => Models\MenuItem::class,
        'menu_item_entry' => Models\MenuItemEntry::class,
        'seo_meta' => Models\SeoMeta::class,
        'redirect' => Models\Redirect::class,
        'request_status' => Models\RequestStatus::class,
        'quote_request' => Models\QuoteRequest::class,
        'contact_message' => Models\ContactMessage::class,
        'job_application' => Models\JobApplication::class,
        'request_activity' => Models\RequestActivity::class,
        'language_line' => LanguageLine::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Custom authentication screens are registered in FortifyServiceProvider.
        Fortify::ignoreRoutes();
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(new Driver));
        $this->app->singleton(SiteCache::class);
        $this->app->singleton(Sitemap::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RouteServiceProvider::loadCachedRoutesUsing(fn () => $this->loadCachedRoutes());
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::preventLazyLoading(! $this->app->isProduction());
        Relation::enforceMorphMap(self::MORPH_MAP);

        $flush = fn () => app(SiteCache::class)->flush();

        Event::listen(SettingsSaved::class, $flush);
        LanguageLine::saved($flush);
        LanguageLine::deleted($flush);
    }
}
