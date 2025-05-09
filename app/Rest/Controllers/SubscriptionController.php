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
            'status' => 'required|string|in:active,inactive,cancelled',
            'contract' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // Handle file upload if contract file is present
        if ($request->hasFile('contract')) {
            $validated['contract'] = $request->file('contract')->store('contracts', 'public');
        }

        // Set billing_date to end of current month if not provided
        $validated['billing_date'] = $request->billing_date ?? Carbon::now()->endOfMonth()->toDateString();

        // Create the subscription
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

    /**
     * Update the specified subscription in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Subscription $subscription
     */
    public function update(Request $request, Subscription $subscription)
    {
        // Validate request data
        $validated = $request->validate([
            'client_id' => 'sometimes|uuid|exists:clients,id',
            'plan_id' => 'sometimes|uuid|exists:plans,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:active,inactive,cancelled',
            'contract' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'billing_date' => 'nullable|date',
        ]);

        // Handle file upload if present
        if ($request->hasFile('contract')) {
            $validated['contract'] = $request->file('contract')->store('contracts', 'public');
        }

        // Update the subscription
        $subscription->update($validated);

        // Return updated resource
        return new SubscriptionResource($subscription);
    }

    /**
     * Remove the specified subscription from storage.
     *
     * @param \App\Models\Subscription $subscription
     */
    public function destroy($subscription)
    {
        $subscription = Subscription::findOrFail($subscription);
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully.']);
    }

    /**
     * Download the contract file associated with the subscription.
     *
     * @param string $id
     */
    public function download($id)
    {
        $subscription = Subscription::findOrFail($id);

        if (!$subscription->contract) {
            return response()->json(['message' => 'No contract found for this subscription'], 404);
        }

        $filePath = storage_path('app/public/' . $subscription->contract);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Contract file not found'], 404);
        }

        return response()->download($filePath);
    }
}
