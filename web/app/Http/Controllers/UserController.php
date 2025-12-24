<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::defaults()],
            'role' => 'required|in:vendor,dcfm,soc,pic',
            'face_image' => 'nullable|string', // Base64 encoded image (optional, enrolled via client)
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'face_image' => $validated['face_image'] ?? null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Load user's tasks if vendor
        if ($user->isVendor()) {
            $user->load(['vendorTasks' => function ($query) {
                $query->latest()->take(10);
            }]);
        }

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ];

        // Only DCFM can change roles
        if ($request->user()->isDcfm()) {
            $rules['role'] = 'sometimes|in:vendor,dcfm,soc';
        }

        // Optional password update
        if ($request->filled('password')) {
            $rules['password'] = ['sometimes', Password::defaults()];
        }

        // Optional face image update
        if ($request->filled('face_image')) {
            $rules['face_image'] = 'sometimes|string';
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Approve a user's face and sync to FastAPI server.
     * This generates the face_embedding server-side.
     */
    public function approve(User $user)
    {
        $this->authorize('update', $user);

        // Check if user has face image to approve
        if (!$user->face_image) {
            return redirect()->route('users.show', $user)
                ->with('error', 'Cannot approve: User has no face image. Please enroll face via client first.');
        }

        // Already approved (has embedding)
        if ($user->face_embedding) {
            return redirect()->route('users.show', $user)
                ->with('info', 'User face is already approved.');
        }

        // Sync to FastAPI server - generates embedding from face_image
        try {
            $serverUrl = env('FASTAPI_SERVER_URL', 'http://127.0.0.1:8001');
            $apiSecret = env('API_SECRET', 'dev-secret');
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post("{$serverUrl}/api/faces/enroll-from-image?secret={$apiSecret}", [
                    'name' => $user->name,
                    'role' => $user->role,
                    'face_image' => $user->face_image,
                ]);

            if ($response->successful()) {
                // Refresh to get updated embedding from database
                $user->refresh();
                return redirect()->route('users.show', $user)
                    ->with('success', 'User face approved and synced successfully.');
            } else {
                \Log::warning("Failed to sync user {$user->id} to FastAPI: " . $response->body());
                return redirect()->route('users.show', $user)
                    ->with('error', 'Failed to sync to FastAPI server: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::warning("Could not sync to FastAPI server: " . $e->getMessage());
            return redirect()->route('users.show', $user)
                ->with('error', 'Could not connect to FastAPI server.');
        }
    }

    /**
     * Reject/unapprove a user's face (clear the embedding).
     */
    public function reject(User $user)
    {
        $this->authorize('update', $user);

        // Clear the embedding to "unapprove"
        $user->face_embedding = null;
        $user->save();

        return redirect()->route('users.show', $user)
            ->with('success', 'User face approval revoked.');
    }
}
