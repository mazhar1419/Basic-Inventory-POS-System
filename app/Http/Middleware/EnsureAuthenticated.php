<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is currently authenticated (logged in).
        // This checks for the existence and validity of the session.
        if (! Auth::check()) {
            
            // If they are NOT logged in, redirect them to the login page.
            // Using route('login') is best practice, but '/login' also works if you named the route.
            return redirect('/login')->with('status', 'Please log in to access this page.');
        }

        // If the user IS logged in, allow the request to proceed to the controller/route.
        return $next($request);
    }
}