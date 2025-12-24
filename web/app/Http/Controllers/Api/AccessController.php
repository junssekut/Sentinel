<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccessValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccessController extends Controller
{
    protected AccessValidationService $validationService;

    public function __construct(AccessValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Validate access request from IoT device.
     *
     * POST /api/access/validate
     *
     * Expected payload:
     * {
     *   "vendor_id": integer,
     *   "pic_id": integer,
     *   "gate_id": "string",
     *   "timestamp": "ISO-8601" (optional)
     * }
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|integer|exists:users,id',
            'pic_id' => 'required|integer|exists:users,id',
            'gate_id' => 'required|string',
            'timestamp' => 'nullable|date',
        ]);

        $result = $this->validationService->validate(
            $validated['vendor_id'],
            $validated['pic_id'],
            $validated['gate_id'],
            $validated['timestamp'] ?? null,
            $request->ip()
        );

        return response()->json($result, $result['approved'] ? 200 : 403);
    }
}
