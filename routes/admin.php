<?php

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

    // System
    Route::get('/users', Admin\System\Users\Index::class)->name('users.index');
    Route::get('/users/create', Admin\System\Users\Form::class)->name('users.create');
    Route::get('/users/{user}/edit', Admin\System\Users\Form::class)->name('users.edit');
    Route::get('/roles', Admin\System\Roles\Index::class)->name('roles.index');
    Route::get('/activity', Admin\System\ActivityLog::class)->name('activity.index');
});
