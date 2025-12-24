<?php

use App\Http\Controllers\Api\AccessController;
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
