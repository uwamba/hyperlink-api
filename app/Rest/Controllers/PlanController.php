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
        $validated = $request->validate([
            'name' => 'required|string|unique:plans,name',
            'price' => 'required|numeric',
            'duration' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $plan = Plan::create($validated);

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
    
}
