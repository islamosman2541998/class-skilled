<?php

use App\Http\Controllers\Admin\BackupDownloadController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\RequestFileController;
use App\Livewire\Admin;
use Illuminate\Support\Facades\Route;

/*
 * Dashboard routes: prefix config('admin.path'), names "admin.*", not localized
 * by URL (dashboard language comes from the user / session).
 */

Route::post('/locale/{locale}', LocaleController::class)->name('locale');
Route::get('/preview/{type}/{id}/{locale}', PreviewController::class)->middleware(['signed', 'auth'])->whereIn('locale', ['ar', 'en'])->name('preview');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/account', Admin\Account::class)->name('account');

    // Requests
    Route::get('/quotes', Admin\Requests\Quotes\Index::class)->name('quotes.index');
    Route::get('/quotes/{id}', Admin\Requests\Quotes\Show::class)->whereNumber('id')->name('quotes.show');
    Route::get('/messages', Admin\Requests\Messages\Index::class)->name('messages.index');
    Route::get('/messages/{id}', Admin\Requests\Messages\Show::class)->whereNumber('id')->name('messages.show');
    Route::get('/applications', Admin\Requests\Applications\Index::class)->name('applications.index');
    Route::get('/applications/{id}', Admin\Requests\Applications\Show::class)->whereNumber('id')->name('applications.show');
    Route::get('/request-statuses', Admin\Requests\Statuses::class)->name('request-statuses.index');
    Route::get('/requests/files/{media}', RequestFileController::class)->middleware('signed')->name('requests.file');

    // Content
    Route::get('/pages', Admin\Content\Pages\Index::class)->name('pages.index');
    Route::get('/pages/create', Admin\Content\Pages\Form::class)->name('pages.create');
    Route::get('/pages/{id}/edit', Admin\Content\Pages\Form::class)->whereNumber('id')->name('pages.edit');
    Route::get('/services', Admin\Content\Services\Index::class)->name('services.index');
    Route::get('/services/create', Admin\Content\Services\Form::class)->name('services.create');
    Route::get('/services/{id}/edit', Admin\Content\Services\Form::class)->whereNumber('id')->name('services.edit');
    Route::get('/projects', Admin\Content\Projects\Index::class)->name('projects.index');
    Route::get('/projects/create', Admin\Content\Projects\Form::class)->name('projects.create');
    Route::get('/projects/{id}/edit', Admin\Content\Projects\Form::class)->whereNumber('id')->name('projects.edit');
    Route::get('/posts', Admin\Content\Posts\Index::class)->name('posts.index');
    Route::get('/posts/create', Admin\Content\Posts\Form::class)->name('posts.create');
    Route::get('/posts/{id}/edit', Admin\Content\Posts\Form::class)->whereNumber('id')->name('posts.edit');
    Route::get('/jobs', Admin\Content\Jobs\Index::class)->name('jobs.index');
    Route::get('/jobs/create', Admin\Content\Jobs\Form::class)->name('jobs.create');
    Route::get('/jobs/{id}/edit', Admin\Content\Jobs\Form::class)->whereNumber('id')->name('jobs.edit');
    Route::get('/home-sections', Admin\Content\HomeSections::class)->name('home-sections.index');
    Route::get('/project-categories', Admin\Content\ProjectCategories::class)->name('project-categories.index');
    Route::get('/post-categories', Admin\Content\PostCategories::class)->name('post-categories.index');
    Route::get('/faqs', Admin\Content\Faqs::class)->name('faqs.index');
    Route::get('/testimonials', Admin\Content\Testimonials::class)->name('testimonials.index');
    Route::get('/clients', Admin\Content\Clients::class)->name('clients.index');
    Route::get('/team', Admin\Content\Team::class)->name('team.index');
    Route::get('/certificates', Admin\Content\Certificates::class)->name('certificates.index');

    // Media
    Route::get('/media', Admin\Media\Library::class)->name('media.index');

    // Navigation & appearance
    Route::get('/menus', Admin\Navigation\Menus::class)->name('menus.index');
    Route::get('/appearance', Admin\Settings\Appearance::class)->name('appearance');

    // SEO
    Route::get('/seo', Admin\Seo\Settings::class)->name('seo.settings');
    Route::get('/seo/health', Admin\Seo\Health::class)->name('seo.health');
    Route::get('/redirects', Admin\Seo\Redirects::class)->name('redirects.index');

    // Settings
    Route::redirect('/settings', '/'.config('admin.path').'/settings/general')->name('settings.index');
    Route::get('/settings/general', Admin\Settings\General::class)->name('settings.general');
    Route::get('/settings/company', Admin\Settings\Company::class)->name('settings.company');
    Route::get('/settings/contact', Admin\Settings\Contact::class)->name('settings.contact');
    Route::get('/settings/social', Admin\Settings\Social::class)->name('settings.social');
    Route::get('/settings/notifications', Admin\Settings\Notifications::class)->name('settings.notifications');
    Route::get('/settings/forms', Admin\Settings\Forms::class)->name('settings.forms');
    Route::get('/settings/media', Admin\Settings\Media::class)->name('settings.media');
    Route::get('/settings/tracking', Admin\Settings\Tracking::class)->name('settings.tracking');
    Route::get('/settings/maintenance', Admin\Settings\Maintenance::class)->name('settings.maintenance');
    Route::get('/settings/system', Admin\Settings\System::class)->name('settings.system');
    Route::get('/ui-strings', Admin\Settings\UiStrings::class)->name('ui-strings.index');

    // System
    Route::get('/users', Admin\System\Users\Index::class)->name('users.index');
    Route::get('/users/create', Admin\System\Users\Form::class)->name('users.create');
    Route::get('/users/{user}/edit', Admin\System\Users\Form::class)->name('users.edit');
    Route::get('/roles', Admin\System\Roles\Index::class)->name('roles.index');
    Route::get('/activity', Admin\System\ActivityLog::class)->name('activity.index');
    Route::get('/backups', Admin\System\Backups::class)->name('backups.index');
    Route::get('/health', Admin\System\Health::class)->name('health');
    Route::get('/backups/{name}', BackupDownloadController::class)->middleware('signed')->name('backups.download');
});
