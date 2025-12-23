<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureVendorApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // If user is a vendor and not approved, show pending approval page
        if ($user && $user->isVendor() && ! $user->isApproved()) {
            // Allow access to logout and pending approval page
            if ($request->routeIs('vendor.pending-approval') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('vendor.pending-approval');
        }
        
        return $next($request);
    }
}
