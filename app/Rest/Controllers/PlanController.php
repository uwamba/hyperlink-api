<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Rest\Resources\PlanResource;


class PlanController extends RestController
{
    /**
     * Display a listing of the plans.
     *
     */
    public function index()
    {
        $plans = Plan::all(); // You can use pagination if needed
        // Return a collection of clients, transformed by the ClientResource
        //return ClientResource::collection(Client::all());
        return PlanResource::collection($plans);
        
    }


    

    /**
     * Store a newly created plan in storage.
     *
     */
    public function store(Request $request)
{
    // Validate incoming request data, including supplier_id and provider_name
    $validated = $request->validate([
        'name' => 'required|string|unique:plans,name',
        'price' => 'required|numeric',
        'provider_price' => 'required|numeric',
        'duration' => 'required|integer',
        'description' => 'nullable|string',
        'supplier_id' => 'required|exists:suppliers,id', // Ensure the supplier exists in the suppliers table
        'provider_name' => 'required|string', // Validate that provider_name is provided
    ]);

    // Create the new plan with the validated data
    $plan = Plan::create([
        'name' => $validated['name'],
        'price' => $validated['price'],
        'provider_price' => $validated['provider_price'],
        'duration' => $validated['duration'],
        'description' => $validated['description'],
        'supplier_id' => $validated['supplier_id'],
        'provider_name' => $validated['provider_name'],
    ]);

    // Return the created plan as a resource
    return new PlanResource($plan);
}

    

    /**
     * Update the specified plan in storage.
     *
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:plans,name,' . $plan->id,
            'price' => 'required|numeric',
            'duration' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $plan->update($validated);

        return new PlanResource($plan);
    }

    /**
     * Remove the specified plan from storage.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
     {
         $invoice = Plan::findOrFail($id);
         $invoice->delete();
 
         return response()->json(['message' => '[Plan deleted successfully'], 200);
     }
}
