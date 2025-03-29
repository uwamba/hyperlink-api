<?php

namespace App\Rest\Controllers;

use App\Models\Subscription;
use App\Rest\Resources\SubscriptionResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Carbon\Carbon;

class SubscriptionController extends RestController
{
    /**
     * Display a listing of the subscriptions.
     *
     */
    public function index()
    {
        // Retrieve all subscriptions
        $subscriptions = Subscription::all();

        // Return the collection of subscriptions as resources
        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Store a newly created subscription in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'required|string',
        ]);

        // Check if billing_date is provided; if not, set it to the last day of the current month
        $validated['billing_date'] = $request->billing_date ?? Carbon::now()->endOfMonth()->toDateString();

        // Create the subscription using validated data
        $subscription = Subscription::create($validated);

        // Return the created subscription as a resource
        return new SubscriptionResource($subscription);
    }

    /**
     * Display the specified subscription.
     *
     * @param \App\Models\Subscription $subscription
     */
    public function show(Subscription $subscription)
    {
        // Return the subscription as a resource
        return new SubscriptionResource($subscription);
    }
}
