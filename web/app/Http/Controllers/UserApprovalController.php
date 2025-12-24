<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserApprovalController extends Controller
{
    /**
     * Show list of pending vendor registrations.
     */
    public function index(): View
    {
        $vendors = User::where('role', 'vendor')
            ->where('face_approved', false)
            ->get();
        return view('vendors.pending', compact('vendors'));
    }

    /**
     * Approve a vendor's face image.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        // Ensure the current user has permission to approve faces
        if (! Gate::allows('approve-faces')) {
            abort(403);
        }

        $user->face_approved = true;
        $user->save();

        return redirect()->route('vendors.pending')->with('status', 'Vendor approved successfully.');
    }
}
