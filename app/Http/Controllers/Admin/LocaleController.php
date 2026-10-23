<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Dashboard language toggle: stored on the user (and in the session for the login screen).
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetAdminLocale::LOCALES, true), 404);

        $request->session()->put('admin_locale', $locale);
        $request->user()?->forceFill(['locale' => $locale])->save();

        return redirect()->back(fallback: route('admin.dashboard'));
    }
}
