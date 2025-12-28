<?php

namespace App\Http\Controllers;

use App\Models\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GateController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of gates.
     */
    public function index()
    {
        $this->authorize('viewAny', Gate::class);

        $gates = Gate::withCount('tasks')->orderBy('name')->paginate(20);

        return view('gates.index', compact('gates'));
    }

    /**
     * Show the form for creating a new gate.
     */
    public function create()
    {
        $this->authorize('create', Gate::class);

        return view('gates.create');
    }

    /**
     * Store a newly created gate.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Gate::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['gate_id'] = 'GATE-' . strtoupper(Str::random(8));
        $validated['is_active'] = $request->boolean('is_active', true);

        Gate::create($validated);

        return redirect()->route('gates.index')
            ->with('success', 'Gate created successfully.');
    }

    /**
     * Display the specified gate.
     */
    public function show(Gate $gate)
    {
        $this->authorize('view', $gate);

        $gate->load(['tasks' => function ($query) {
            $query->with(['vendors', 'pic'])->latest()->take(10);
        }]);

        return view('gates.show', compact('gate'));
    }

    /**
     * Show the form for editing the specified gate.
     */
    public function edit(Gate $gate)
    {
        $this->authorize('update', $gate);

        return view('gates.edit', compact('gate'));
    }

    /**
     * Update the specified gate.
     */
    public function update(Request $request, Gate $gate)
    {
        $this->authorize('update', $gate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Handle door integration fields (SOC can manage these)
        $user = $request->user();
        if ($user && $user->isSoc()) {
            $doorValidated = $request->validate([
                'door_id' => 'nullable|string|max:255|unique:gates,door_id,' . $gate->id,
                'door_ip_address' => 'nullable|ip',
            ]);
            
            $validated = array_merge($validated, $doorValidated);
            
            // Update integration status based on door_id
            if (!empty($validated['door_id'])) {
                $validated['integration_status'] = $gate->last_heartbeat_at ? 'integrated' : 'not_integrated';
            } else {
                $validated['integration_status'] = 'not_integrated';
                $validated['door_ip_address'] = null;
            }
        }

        $gate->update($validated);

        return redirect()->route('gates.show', $gate)
            ->with('success', 'Gate updated successfully.');
    }

    /**
     * Remove the specified gate.
     */
    public function destroy(Gate $gate)
    {
        $this->authorize('delete', $gate);

        $gate->delete();

        return redirect()->route('gates.index')
            ->with('success', 'Gate deleted successfully.');
    }
}
