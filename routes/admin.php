<?php

use App\Http\Controllers\Admin\BackupDownloadController;
use App\Http\Controllers\Admin\LocaleController;
use App\Livewire\Admin;
use Illuminate\Support\Facades\Route;

/*
 * Dashboard routes: prefix config('admin.path'), names "admin.*", not localized
 * by URL (dashboard language comes from the user / session).
 */

Route::post('/locale/{locale}', LocaleController::class)->name('locale');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/account', Admin\Account::class)->name('account');

    // Media
    Route::get('/media', Admin\Media\Library::class)->name('media.index');

    // Navigation & appearance
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
    Route::get('/backups/{name}', BackupDownloadController::class)->middleware('signed')->name('backups.download');
});
