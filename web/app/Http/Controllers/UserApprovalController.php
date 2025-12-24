<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserApprovalController extends Controller
{
    /**
     * Show list of pending vendor registrations (have face_image but no face_embedding).
     */
    public function index(): View
    {
        $vendors = User::where('role', 'vendor')
            ->whereNotNull('face_image')
            ->whereNull('face_embedding')
            ->get();
        return view('vendors.pending', compact('vendors'));
    }

    /**
     * Approve a vendor's face image and sync to FastAPI server.
     * This generates the face_embedding server-side.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        // Ensure the current user has permission to approve faces
        if (! Gate::allows('approve-faces')) {
            abort(403);
        }

        // Check if user has face image to approve
        if (!$user->face_image) {
            return redirect()->route('vendors.pending')
                ->with('error', 'Cannot approve: User has no face image. Please enroll face via client first.');
        }

        // Already approved (has embedding)
        if ($user->face_embedding) {
            return redirect()->route('vendors.pending')
                ->with('info', 'Vendor is already approved.');
        }

        // Sync to FastAPI server - this generates the embedding
        $success = $this->syncToFastAPIServer($user);

        if ($success) {
            return redirect()->route('vendors.pending')
                ->with('status', 'Vendor approved and synced successfully.');
        } else {
            return redirect()->route('vendors.pending')
                ->with('error', 'Failed to sync to FastAPI server. Please try again.');
        }
    }

    /**
     * Sync user face data to FastAPI server.
     * FastAPI generates the embedding from face_image and stores it.
     */
    private function syncToFastAPIServer(User $user): bool
    {
        try {
            $serverUrl = env('FASTAPI_SERVER_URL', 'http://127.0.0.1:8001');
            $apiSecret = env('API_SECRET', 'dev-secret');

            // Call the endpoint that generates embedding server-side from face_image
            $response = Http::timeout(30)->post("{$serverUrl}/api/faces/enroll-from-image?secret={$apiSecret}", [
                'name' => $user->name,
                'role' => $user->role,
                'face_image' => $user->face_image,
            ]);

            if ($response->successful()) {
                // Refresh user to get the updated face_embedding from database
                $user->refresh();
                Log::info("User {$user->id} ({$user->name}) synced to FastAPI server successfully.");
                return true;
            } else {
                Log::warning("Failed to sync user {$user->id} to FastAPI: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::warning("Could not sync to FastAPI server: " . $e->getMessage());
            return false;
        }
    }
}
