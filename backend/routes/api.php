<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\MessagesController;
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
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
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
    Route::apiResource('conversations', ConversationsController::class);
    Route::apiResource('messages', MessagesController::class)->only(['index', 'store', 'show']);

    Route::post('/conversations/{conversation}/request-agent', [ConversationsController::class, 'requestAgent']);
    Route::post('/conversations/{conversation}/accept', [ConversationsController::class, 'accept'])
        ->middleware('role:helpdesk_agent');
    Route::post('/conversations/{conversation}/close', [ConversationsController::class, 'close'])
        ->middleware('role:helpdesk_agent');
    Route::get('/helpdesk/conversations/queue', [ConversationsController::class, 'queue'])
        ->middleware('role:helpdesk_agent');

    Route::get('/home/help-panel', function () {
        return response()->json([
            'message' => 'Help panel data placeholder.',
        ]);
    })->middleware('role:user,admin');

    Route::get('/home/helpdesk-chat-panel', function () {
        return response()->json([
            'message' => 'Helpdesk chat panel data placeholder.',
        ]);
    })->middleware('role:helpdesk_agent,admin');
});
