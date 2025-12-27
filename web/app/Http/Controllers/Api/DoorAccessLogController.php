<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoorAccessLog;
use App\Models\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoorAccessLogController extends Controller
{
    /**
     * Log a door access event from the Python server.
     * 
     * POST /api/doors/log-access
     */
    public function logAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'door_id' => 'required|string',
            'event_type' => 'required|in:entry,exit,denied',
            'vendor_id' => 'nullable|integer',
            'pic_id' => 'nullable|integer',
            'task_id' => 'nullable|integer',
            'session_id' => 'nullable|string',
            'reason' => 'nullable|string',
            'details' => 'nullable|array',
        ]);

        // Find gate by door_id
        $gate = Gate::where('door_id', $validated['door_id'])->first();
        
        if (!$gate) {
            return response()->json([
                'success' => false,
                'error' => 'Gate not found for door_id: ' . $validated['door_id']
            ], 404);
        }

        // Create the access log
        $log = DoorAccessLog::create([
            'gate_id' => $gate->id,
            'task_id' => $validated['task_id'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'pic_id' => $validated['pic_id'] ?? null,
            'event_type' => $validated['event_type'],
            'reason' => $validated['reason'] ?? null,
            'details' => $validated['details'] ?? null,
            'session_id' => $validated['session_id'] ?? null,
            'client_ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
        ]);
    }

    /**
     * Get access logs for a specific gate (for polling).
     * 
     * GET /api/gates/{gate}/access-logs
     */
    public function index(Gate $gate, Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);
        $since = $request->input('since'); // timestamp for incremental updates
        
        $query = $gate->accessLogs()
            ->with(['vendor:id,name', 'pic:id,name', 'task:id,status'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);
        
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        
        $logs = $query->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'event_type' => $log->event_type,
                'vendor' => $log->vendor ? $log->vendor->name : null,
                'pic' => $log->pic ? $log->pic->name : null,
                'reason' => $log->reason,
                'session_id' => $log->session_id,
                'created_at' => $log->created_at->toIso8601String(),
                'created_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'gate_id' => $gate->id,
            'door_id' => $gate->door_id,
            'integration_status' => $gate->integration_status,
            'is_online' => $gate->isOnline(),
            'logs' => $logs,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Receive heartbeat from IoT device.
     * 
     * POST /api/doors/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'door_id' => 'required|string',
            'status' => 'nullable|string',
        ]);

        $gate = Gate::where('door_id', $validated['door_id'])->first();
        
        if (!$gate) {
            return response()->json([
                'success' => false,
                'error' => 'Gate not found'
            ], 404);
        }

        $gate->update([
            'last_heartbeat_at' => now(),
            'integration_status' => 'integrated',
        ]);

        return response()->json([
            'success' => true,
            'gate_id' => $gate->id,
        ]);
    }

    /**
     * Get gate info by door_id (for Python server).
     * 
     * GET /api/doors/{door_id}/info
     */
    public function gateInfo(string $doorId): JsonResponse
    {
        $gate = Gate::where('door_id', $doorId)
            ->with(['tasks' => function ($query) {
                $query->where('status', 'active')
                      ->where('start_time', '<=', now())
                      ->where('end_time', '>=', now());
            }])
            ->first();
        
        if (!$gate) {
            return response()->json([
                'success' => false,
                'error' => 'Gate not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'gate' => [
                'id' => $gate->id,
                'name' => $gate->name,
                'gate_id' => $gate->gate_id,
                'door_id' => $gate->door_id,
                'door_ip_address' => $gate->door_ip_address,
                'is_active' => $gate->is_active,
                'integration_status' => $gate->integration_status,
                'active_tasks_count' => $gate->tasks->count(),
            ],
        ]);
    }
}
