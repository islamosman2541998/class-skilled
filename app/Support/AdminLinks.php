<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;

/**
 * Dashboard URLs for any model (used by "used in" lists, SEO health, search).
 * Returns null while the module's screen does not exist.
 */
class AdminLinks
{
    /** morph alias => [route name, label key] */
    public const EDIT_ROUTES = [
        'page' => ['admin.pages.edit', 'admin.nav.pages'],
        'service' => ['admin.services.edit', 'admin.nav.services'],
        'project' => ['admin.projects.edit', 'admin.nav.projects'],
        'project_category' => ['admin.project-categories.index', 'admin.nav.project_categories'],
        'post' => ['admin.posts.edit', 'admin.nav.posts'],
        'post_category' => ['admin.post-categories.index', 'admin.nav.post_categories'],
        'faq' => ['admin.faqs.index', 'admin.nav.faqs'],
        'testimonial' => ['admin.testimonials.index', 'admin.nav.testimonials'],
        'client' => ['admin.clients.index', 'admin.nav.clients'],
        'team_member' => ['admin.team.index', 'admin.nav.team'],
        'certificate' => ['admin.certificates.index', 'admin.nav.certificates'],
        'job_post' => ['admin.jobs.edit', 'admin.nav.jobs'],
        'home_section' => ['admin.home-sections.index', 'admin.nav.home_sections'],
        'asset' => ['admin.media.index', 'admin.nav.library'],
        'settings' => ['admin.settings.index', 'admin.nav.settings'],
    ];

    public static function editUrl(string $type, int|string|null $id = null): ?string
    {
        $route = self::EDIT_ROUTES[$type][0] ?? null;

        if ($route === null || ! Route::has($route)) {
            return null;
        }

        $parameters = Route::getRoutes()->getByName($route)?->parameterNames() ?? [];

        return $parameters === [] || $id === null ? route($route) : route($route, [$parameters[0] => $id]);
    }

    public static function typeLabel(string $type): string
    {
        return isset(self::EDIT_ROUTES[$type]) ? __(self::EDIT_ROUTES[$type][1]) : $type;
    }

    /**
     * Human title of a model in the dashboard language.
     */
    public static function title(?Model $model): string
    {
        if ($model === null) {
            return '';
        }

        foreach (['title', 'name', 'question', 'number', 'original_name'] as $field) {
            if (! array_key_exists($field, $model->getAttributes())) {
                continue;
            }

            $value = $model->getAttribute($field);

            if (is_array($value)) {
                $value = $value[app()->getLocale()] ?? $value['ar'] ?? reset($value);
            }

            if (filled($value) && is_scalar($value)) {
                return (string) $value;
            }
        }

        return '#'.$model->getKey();
    }

    public static function model(string $type, int $id): ?Model
    {
        $class = Relation::getMorphedModel($type);

        return $class && is_subclass_of($class, Model::class) ? $class::query()->find($id) : null;
    }
}
