<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function resend(Request $request)
    {
        $user = auth('sanctum')->user() ?? $request->user();
        $user->regenerateTwoFactorCode();
        $user->notify(new TwoFactorCodeNotification());

        return response()->json([
            'message' => 'Two factor code has been resent'
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'integer|required',
        ]);

        $user = auth('sanctum')->user() ?? $request->user();

        if ($user->two_factor_code !== $request->code) {
            return response()->json([
                'message' => 'The two factor code you have entered is invalid.'
            ], 400);
        }

        if ($user->two_factor_expires_at < now()) {
            return response()->json([
                'message' => 'The two factor code has expired.'
            ], 400);
        }

        $user->resetTwoFactorCode();

        return response()->json([
            'message' => 'Two factor authentication verified.',
            'user' => $user,
        ]);
    }
}
