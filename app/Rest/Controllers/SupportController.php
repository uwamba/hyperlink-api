<?php

namespace App\Rest\Controllers;

// app/Http/Controllers/SupportController.php

use App\Models\Support;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController; 
use App\Rest\Resources\SupportResource;    
class SupportController extends RestController
{
    public function create(Request $request)
{
    $validated = $request->validate([
        'client_name' => 'required|string|max:255',
        'client_email' => 'required|email|max:255',
        'client_phone' => 'required|string|max:15',
        'inquiry' => 'required|string',
    ]);

    $support = Support::create([
        'client_name' => $validated['client_name'],
        'client_email' => $validated['client_email'],
        'client_phone' => $validated['client_phone'],
        'inquiry' => $validated['inquiry'],
        'status' => 'logged',
    ]);

    // Return the support inquiry as a resource
    return new SupportResource($support);
}

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:logged,active,resolved,closed',
        ]);

        $support = Support::findOrFail($id);
        $support->status = $validated['status'];
        $support->save();

        return response()->json(['message' => 'Support status updated successfully', 'support' => $support]);
    }

    public function index()
{
    $supports = Support::all();

    // Return a collection of supports as resources
    return  SupportResource::collection($supports);
}

    public function show($id)
    {
        // Fetch a specific support inquiry
        $support = Support::findOrFail($id);
        
        return  SupportResource::collection($support);
    }
}
