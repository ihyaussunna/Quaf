<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this section.');
        }

        $user = Auth::user();

        if ($user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Locked: നിങ്ങളുടെ അക്കൗണ്ട് അഡ്മിൻ ലോക്ക് ചെയ്തിരിക്കുന്നു (Account Locked).']);
        }

        // Super Admin has universal access
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Check if user's role matches any allowed role
        if (! in_array($user->role, $roles)) {
            // If user is admin and route allows 'admin', let's allow sub-admin roles if appropriate
            if (in_array('admin', $roles) && in_array($user->role, ['admin', 'program_coordinator', 'stage_coordinator', 'program_committee'])) {
                return $next($request);
            }

            if (in_array('media_team', $roles) && in_array($user->role, ['media_team', 'media_manager', 'admin'])) {
                return $next($request);
            }

            abort(403, 'Unauthorized. You do not have permission to access this management area.');
        }

        return $next($request);
    }
}
