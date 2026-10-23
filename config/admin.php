<?php

return [
    'path' => env('ADMIN_PATH', 'cs-panel'),
    'name' => env('ADMIN_NAME'),
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),
    'version' => '1.0',

    /*
     * Sidebar. An item is shown only when its route exists (modules of later
     * phases simply have no route yet) and the user passes `can`.
     * `active` is a route-name pattern; `badge` is a key of AdminBadges::counts().
     */
    'navigation' => [
        ['label' => 'nav.home', 'route' => 'admin.dashboard', 'can' => 'view dashboard', 'active' => 'admin.dashboard'],
        ['group' => 'requests', 'label' => 'nav.requests', 'items' => [
            ['label' => 'nav.quotes', 'route' => 'admin.quotes.index', 'can' => 'view quote_requests', 'active' => 'admin.quotes.*', 'badge' => 'quotes'],
            ['label' => 'nav.messages', 'route' => 'admin.messages.index', 'can' => 'view contact_messages', 'active' => 'admin.messages.*', 'badge' => 'messages'],
            ['label' => 'nav.applications', 'route' => 'admin.applications.index', 'can' => 'view job_applications', 'active' => 'admin.applications.*', 'badge' => 'applications'],
            ['label' => 'nav.request_statuses', 'route' => 'admin.request-statuses.index', 'can' => 'manage request statuses', 'active' => 'admin.request-statuses.*'],
        ]],
        ['group' => 'content', 'label' => 'nav.content', 'items' => [
            ['label' => 'nav.home_sections', 'route' => 'admin.home-sections.index', 'can' => 'view home_sections', 'active' => 'admin.home-sections.*'],
            ['label' => 'nav.pages', 'route' => 'admin.pages.index', 'can' => 'view pages', 'active' => 'admin.pages.*'],
            ['label' => 'nav.services', 'route' => 'admin.services.index', 'can' => 'view services', 'active' => 'admin.services.*'],
            ['label' => 'nav.projects', 'route' => 'admin.projects.index', 'can' => 'view projects', 'active' => 'admin.projects.*'],
            ['label' => 'nav.project_categories', 'route' => 'admin.project-categories.index', 'can' => 'view project_categories', 'active' => 'admin.project-categories.*'],
            ['label' => 'nav.posts', 'route' => 'admin.posts.index', 'can' => 'view posts', 'active' => 'admin.posts.*'],
            ['label' => 'nav.post_categories', 'route' => 'admin.post-categories.index', 'can' => 'view post_categories', 'active' => 'admin.post-categories.*'],
            ['label' => 'nav.faqs', 'route' => 'admin.faqs.index', 'can' => 'view faqs', 'active' => 'admin.faqs.*'],
            ['label' => 'nav.testimonials', 'route' => 'admin.testimonials.index', 'can' => 'view testimonials', 'active' => 'admin.testimonials.*'],
            ['label' => 'nav.clients', 'route' => 'admin.clients.index', 'can' => 'view clients', 'active' => 'admin.clients.*'],
            ['label' => 'nav.team', 'route' => 'admin.team.index', 'can' => 'view team_members', 'active' => 'admin.team.*'],
            ['label' => 'nav.certificates', 'route' => 'admin.certificates.index', 'can' => 'view certificates', 'active' => 'admin.certificates.*'],
            ['label' => 'nav.jobs', 'route' => 'admin.jobs.index', 'can' => 'view job_posts', 'active' => 'admin.jobs.*'],
        ]],
        ['group' => 'navigation', 'label' => 'nav.navigation', 'open' => false, 'items' => [
            ['label' => 'nav.menus', 'route' => 'admin.menus.index', 'can' => 'view menus', 'active' => 'admin.menus.*'],
            ['label' => 'nav.appearance', 'route' => 'admin.appearance', 'can' => 'manage appearance', 'active' => 'admin.appearance'],
        ]],
        ['group' => 'media', 'label' => 'nav.media', 'open' => false, 'items' => [
            ['label' => 'nav.library', 'route' => 'admin.media.index', 'can' => 'view media', 'active' => 'admin.media.*'],
        ]],
        ['group' => 'seo', 'label' => 'nav.seo', 'open' => false, 'items' => [
            ['label' => 'nav.seo_settings', 'route' => 'admin.seo.settings', 'can' => 'manage seo', 'active' => 'admin.seo.settings'],
            ['label' => 'nav.redirects', 'route' => 'admin.redirects.index', 'can' => 'view redirects', 'active' => 'admin.redirects.*'],
            ['label' => 'nav.seo_health', 'route' => 'admin.seo.health', 'can' => 'manage seo', 'active' => 'admin.seo.health'],
        ]],
        ['group' => 'settings', 'label' => 'nav.settings', 'open' => false, 'items' => [
            ['label' => 'nav.general_settings', 'route' => 'admin.settings.index', 'can' => 'manage settings', 'active' => 'admin.settings.*'],
            ['label' => 'nav.ui_strings', 'route' => 'admin.ui-strings.index', 'can' => 'manage ui strings', 'active' => 'admin.ui-strings.*'],
        ]],
        ['group' => 'system', 'label' => 'nav.system', 'open' => false, 'items' => [
            ['label' => 'nav.users', 'route' => 'admin.users.index', 'can' => 'manage users', 'active' => 'admin.users.*'],
            ['label' => 'nav.roles', 'route' => 'admin.roles.index', 'can' => 'manage users', 'active' => 'admin.roles.*'],
            ['label' => 'nav.activity', 'route' => 'admin.activity.index', 'can' => 'view activity', 'active' => 'admin.activity.*'],
            ['label' => 'nav.backups', 'route' => 'admin.backups.index', 'can' => 'manage backups', 'active' => 'admin.backups.*'],
            ['label' => 'nav.health', 'route' => 'admin.health', 'can' => 'manage settings', 'active' => 'admin.health'],
        ]],
    ],
];
