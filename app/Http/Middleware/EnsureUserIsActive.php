<?php

namespace App\Http\Middleware;

use App\Settings\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out deactivated users and dashboard sessions idle for longer than
 * the configured timeout (SystemSettings::admin_session_minutes).
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $minutes = max(10, app(SystemSettings::class)->admin_session_minutes);
        $lastSeen = (int) $request->session()->get('admin_last_seen', time());
        $expired = time() - $lastSeen > $minutes * 60;

        if (! $user->getAttribute('is_active') || $expired) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $expired ? __('admin.auth.session_expired') : __('admin.auth.inactive'),
            ]);
        }

        $request->session()->put('admin_last_seen', time());

        return $next($request);
    }
}
