<?php

namespace App\Providers;

use App\Models;
use App\Services\SiteCache;
use App\Services\Sitemap;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
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
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Administrators pass every check except the safety rules on user accounts.
        Gate::before(function (Models\User $user, string $ability, array $arguments) {
            $target = $arguments[0] ?? null;

            if ($target instanceof Models\User || $target === Models\User::class) {
                return null;
            }

            return $user->hasRole('admin') ? true : null;
        });

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof Models\User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                session()->put('admin_last_seen', time());
            }
        });

        $flush = fn () => app(SiteCache::class)->flush();

        Event::listen(SettingsSaved::class, $flush);
        LanguageLine::saved($flush);
        LanguageLine::deleted($flush);
    }
}
