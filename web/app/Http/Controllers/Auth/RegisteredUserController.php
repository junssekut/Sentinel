<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'face_image' => ['required', 'string'], // Base64 image string
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
            'face_image' => $request->face_image,
        ]);

        event(new Registered($user));

        // Auto-enroll face to FastAPI for embedding generation
        $this->enrollFaceToFastAPI($user);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Enroll user's face to FastAPI server for embedding generation.
     * This runs asynchronously - the embedding will be generated in background.
     * 
     * Note: The user will need DCFM approval (account activation) before
     * they can use the face scanner, even though embedding is generated automatically.
     */
    private function enrollFaceToFastAPI(User $user): void
    {
        try {
            $serverUrl = env('FASTAPI_SERVER_URL', 'http://127.0.0.1:8001');
            $apiSecret = env('API_SECRET', 'dev-secret');

            // Fire and forget - don't block the registration flow
            Http::timeout(5)
                ->async()
                ->post("{$serverUrl}/api/faces/enroll-from-image?secret={$apiSecret}", [
                    'name' => $user->name,
                    'role' => $user->role,
                    'face_image' => $user->face_image,
                ])
                ->then(function ($response) use ($user) {
                    if ($response->successful()) {
                        Log::info("Face auto-enrolled for new user {$user->id} ({$user->name})");
                    } else {
                        Log::warning("Failed to auto-enroll face for user {$user->id}: " . $response->body());
                    }
                })
                ->catch(function ($e) use ($user) {
                    Log::warning("Could not connect to FastAPI for user {$user->id}: " . $e->getMessage());
                });

        } catch (\Exception $e) {
            // Don't fail registration if FastAPI is unavailable
            Log::warning("Exception during face enrollment for user {$user->id}: " . $e->getMessage());
        }
    }
}
