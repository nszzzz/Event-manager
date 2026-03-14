<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public (guest) routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');

// Authenticated but may still need 2FA (no two.factor middleware)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'two_factor_required' => $user->two_factor_code !== null,
        ]);
    });

    Route::post('/verify', [TwoFactorController::class, 'verify']);
    Route::post('/verify/resend', [TwoFactorController::class, 'resend']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Fully authenticated (2FA verified) routes
Route::middleware(['auth:sanctum', 'two.factor'])->group(function () {
    Route::apiResource('events', EventsController::class);
});
