<?php

namespace App\Rest\Controllers;


use App\Models\Support;
use App\Rest\Resources\SupportResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Models\EmailVerification;
use App\Notifications\SendVerificationOtp;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;


use App\Models\Client;




class SupportController extends RestController
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SupportResource::collection(Support::all());
    }

    /**
     * Store a newly created resource in storage.
     */
 





public function store(Request $request)
{
    $data = $request->validate([
        'client_id' => 'nullable|uuid',
        'email' => 'required|email',
        'issue' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'description' => 'required|string|max:2000',
        'address' => 'nullable|string',
        'name' => 'nullable|string|max:255', // For new client registration
        'phone' => 'nullable|string|max:20', // For new client registration
    ]);

    // Check if client exists
    $client = Client::where('email', $data['email'])->first();

    if (!$client) {
        // Client doesn't exist - need to verify email and register
        $verified = EmailVerification::where('email', $data['email'])
            ->whereNotNull('verified_at')
            ->exists();

        if (!$verified) {
            // Generate OTP
            $otp = rand(100000, 999999);
            
            // Store or update verification record
            EmailVerification::updateOrCreate(
                ['email' => $data['email']],
                [
                    'otp' => $otp,
                    'expires_at' => Carbon::now()->addMinutes(30),
                    'verified_at' => null
                ]
            );

            try {
                Notification::route('mail', $data['email'])
                    ->notify(new SendVerificationOtp($otp));

                return response()->json([
                    'message' => 'OTP sent to email. Please verify to register and create support request.',
                    'requires_verification' => true,
                    'email' => $data['email'],
                    'is_new_client' => true
                ], 202);
            } catch (\Exception $e) {
                \Log::error('OTP sending failed: '.$e->getMessage());
                return response()->json([
                    'message' => 'Failed to send OTP. Please try again.',
                    'error' => 'Mail sending failed'
                ], 500);
            }
        }

        // Email is verified but client doesn't exist - register new client
        $client = Client::create([
            'name' => $data['name'] ?? 'Unknown', // Default name if not provided
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'created_by' => auth()->id() ?? null,
            'updated_by' => auth()->id() ?? null,
        ]);
    }

    // Now create the support ticket with the client (existing or new)
    $supportData = [
        'client_id' => $client->id,
        'email' => $data['email'],
        'issue' => $data['issue'],
        'category' => $data['category'],
        'description' => $data['description'],
        'address' => $data['address'] ?? $client->address,
    ];

    $support = Support::create($supportData);

    return response()->json([
        'message' => 'Support ticket created successfully',
        'data' => $support
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Support $support)
    {
        return new SupportResource($support);
    }

    /**
     * Update the specified resource in storage.
     *
     * @method PATCH
 
     */
    public function update(Request $request, Support $support)
    {

        $validated = $request->validate([
            'client_id' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'description' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $support->update($validated);

        return new SupportResource($support);
    }

    public function destroy($support)
    {
        $support = Support::find($support);

        if (!$support) {
            return response()->json(['message' => 'Support request not found'], 404);
        }

        $support->delete();


        return response()->json(['message' => 'Support deleted successfully'], 200);
    }


    public function verifyEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|string',
    ]);

    $verification = EmailVerification::where('email', $request->email)
        ->where('otp', $request->otp)
        ->where('expires_at', '>', now())
        ->first();

    if (!$verification) {
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    $verification->update(['verified_at' => now()]);

    // Optional: Update all support requests with this email to verified status
    Support::where('email', $request->email)
        ->update(['email_verified_at' => now()]);

    return response()->json(['message' => 'Email verified successfully']);
}


    /**
     * Remove the specified resource from storage.
     */
}
