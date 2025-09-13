<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if ($user && in_array($user->role, $roles)) {
            return $next($request);
        }

        // Logout the user if role is not allowed
        Auth::logout();

        // Invalidate the session & regenerate token for security
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with message
        return redirect()->route('login')->withErrors([
            'auth' => 'You are not authorized to access this area.',
        ]);
    }
}
