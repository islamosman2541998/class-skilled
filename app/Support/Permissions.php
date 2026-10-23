<?php

namespace App\Support;

/**
 * Permission catalogue and default role grants.
 * Module permissions are named "{action} {module}", e.g. "update projects".
 * The `admin` role passes every check through Gate::before.
 */
class Permissions
{
    public const ACTIONS = ['view', 'create', 'update', 'delete'];

    public const CONTENT_MODULES = [
        'pages', 'services', 'projects', 'project_categories', 'posts', 'post_categories', 'faqs',
        'testimonials', 'clients', 'team_members', 'certificates', 'job_posts', 'home_sections',
        'menus', 'media', 'redirects',
    ];

    public const REQUEST_MODULES = ['quote_requests', 'contact_messages', 'job_applications'];

    public const SPECIAL = [
        'view dashboard',
        'manage settings',
        'manage appearance',
        'manage seo',
        'manage ui strings',
        'manage request statuses',
        'export requests',
        'export content',
        'manage users',
        'view activity',
        'manage backups',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $names = [];

        foreach ([...self::CONTENT_MODULES, ...self::REQUEST_MODULES] as $module) {
            foreach (self::ACTIONS as $action) {
                $names[] = $action.' '.$module;
            }
        }

        return [...$names, ...self::SPECIAL];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function roles(): array
    {
        $content = [];

        foreach (self::CONTENT_MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                $content[] = $action.' '.$module;
            }
        }

        $requests = [];

        foreach (self::REQUEST_MODULES as $module) {
            $requests[] = 'view '.$module;
            $requests[] = 'update '.$module;
        }

        return [
            'admin' => self::all(),
            'editor' => [...$content, 'view dashboard', 'manage appearance', 'manage seo', 'manage ui strings', 'export content'],
            'sales' => [...$requests, 'view dashboard', 'export requests'],
        ];
    }
}
