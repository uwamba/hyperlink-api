<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Notifications\ResetPassword;

class ForgotPasswordController extends Controller
{
    /**
     * Send the password reset link to the given email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     */
    public function sendResetLinkEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    ResetPassword::createUrlUsing(function ($user, string $token) {
        return url("/reset-password?token=$token&email=" . urlencode($user->email));
    });

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'message' => __($status),
        ], 200);
    }

    throw ValidationException::withMessages([
        'email' => [__($status)],
    ]);
}

}
