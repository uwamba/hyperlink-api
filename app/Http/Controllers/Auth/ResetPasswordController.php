<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Handle a reset password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
{
    // Log incoming request data for debugging
    \Log::info('Password reset request:', $request->all());

    // Validate the request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|confirmed|min:6',
        'token' => 'required',
    ]);

    // Attempt password reset
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) use ($request) {
            $user->password = Hash::make($request->password);
            $user->save();
        }
    );


    // Return appropriate JSON response
    if($status == Password::PASSWORD_RESET) {
        return response()->json([
            'message' => 'Password has been reset successfully!'
        ], 200);
    } else {
        return response()->json([
            'message' => 'Failed to reset password.',
            'error' => __($status)
        ], 400);
    }
}

}
