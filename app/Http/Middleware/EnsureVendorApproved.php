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
        if ($user && $user->isVendor() && ! $user->isApproved()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account is pending approval by DCFM or SOC.');
        }
        return $next($request);
    }
}
