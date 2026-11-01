<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for access token in query parameter or session
        $validToken = env('FMS_ACCESS_TOKEN', 'UA-FMS-ACCESS-2025');
        $requestedRole = $request->get('role');
        
        // Validate access token first
        if ($request->get('access_token') !== $validToken && session('login_access') !== true) {
            // Vague error - doesn't reveal why access failed
            abort(404);
        }
         
        // If role is specified, validate it
        if ($requestedRole && !in_array($requestedRole, ['admin', 'college_staff', 'org_staff'])) {
            // Clear any existing session and show vague error
            session()->forget('login_access');
            abort(404);
        }
        
        // Store requested role in session for validation during login
        if ($requestedRole) {
            session(['required_role' => $requestedRole]);
        }
        
        // Set session flag for subsequent requests
        session(['login_access' => true]);
        return $next($request);
    }
}
