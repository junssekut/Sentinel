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
     *   "vendor_face_id": "string",
     *   "pic_face_id": "string",
     *   "gate_id": "string",
     *   "timestamp": "ISO-8601" (optional)
     * }
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_face_id' => 'required|string',
            'pic_face_id' => 'required|string',
            'gate_id' => 'required|string',
            'timestamp' => 'nullable|date',
        ]);

        $result = $this->validationService->validate(
            $validated['vendor_face_id'],
            $validated['pic_face_id'],
            $validated['gate_id'],
            $validated['timestamp'] ?? null,
            $request->ip()
        );

        return response()->json($result, $result['approved'] ? 200 : 403);
    }
}
