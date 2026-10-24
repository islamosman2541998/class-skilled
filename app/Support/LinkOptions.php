<?php

namespace App\Support;

use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

/**
 * Choices for the dashboard link picker: system routes and linkable models
 * (titles in the dashboard language).
 */
class LinkOptions
{
    /** @var array<string, class-string<Model>> */
    public const MODELS = [
        'page' => Page::class,
        'service' => Service::class,
        'project' => Project::class,
        'project_category' => ProjectCategory::class,
        'post' => Post::class,
        'post_category' => PostCategory::class,
        'job_post' => JobPost::class,
    ];

    /**
     * Dashboard label of a system page. Route names contain dots, which the translator would read as nesting,
     * so the keys in lang/<locale>/admin.php use underscores (services.index => services_index).
     */
    public static function routeLabel(string $route): string
    {
        return (string) __('admin.link.routes.'.str_replace('.', '_', $route));
    }

    /**
     * @return array{routes: array<string, string>, models: array<string, array{label: string, items: array<int, string>}>}
     */
    public static function all(): array
    {
        $locale = app()->getLocale();
        $routes = [];

        foreach (LinkResolver::SYSTEM_ROUTES as $route) {
            $routes[$route] = self::routeLabel($route);
        }

        $models = [];

        foreach (self::MODELS as $type => $class) {
            $field = in_array('title', (new $class)->getTranslatableAttributes(), true) ? 'title' : 'name';
            $items = [];

            foreach ($class::query()->orderBy('id')->get(['id', $field]) as $model) {
                $items[(int) $model->getKey()] = (string) ($model->getTranslation($field, $locale, false) ?: $model->getTranslation($field, 'ar', false));
            }

            $models[$type] = ['label' => __('admin.link.models.'.$type), 'items' => $items];
        }

        return ['routes' => $routes, 'models' => $models];
    }
}
