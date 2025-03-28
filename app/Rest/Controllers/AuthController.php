<?php

namespace App\Rest\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use App\Rest\Controller as RestController;

use Illuminate\Validation\ValidationException;
use Exception;


class AuthController extends RestController
{
    public function register(Request $request)
{
    try {
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
        if (!$user) {
            return response()->json(['error' => 'User creation failed'], 500);
        }

        // Generate API token
        $token = $user->createToken('API Token')->accessToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    } catch (ValidationException $e) {
        return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
    } catch (Exception $e) {
        return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
    }
}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->accessToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
