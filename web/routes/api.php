<?php

use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\DoorAccessLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// IoT Access Validation Endpoint
// This endpoint is called by IoT face-scanning devices to validate access
Route::post('/access/validate', [AccessController::class, 'validate']);

// Door Access Log Endpoints (for Python server integration)
Route::post('/doors/log-access', [DoorAccessLogController::class, 'logAccess']);
Route::post('/doors/heartbeat', [DoorAccessLogController::class, 'heartbeat']);
Route::get('/doors/{door_id}/info', [DoorAccessLogController::class, 'gateInfo']);

// Gate Access Logs (for website polling)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/gates/{gate}/access-logs', [DoorAccessLogController::class, 'index']);
});

