<?php 

namespace App\Rest\Controllers;

use App\Models\Billing;
use App\Rest\Resources\BillingResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;

class BillingController extends RestController
{
    /**
     * Display a listing of the billings.
     *
     */
    public function index()
    {
        // Retrieve all billing records
        $billings = Billing::all();

        // Return the collection of billings as resources
        return BillingResource::collection($billings);
    }

    /**
     * Store a newly created billing in storage.
     *
     * @param \Illuminate\Http\Request $request

     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'invoice_id' => 'required|string|unique:billings,invoice_id',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid,overdue',
        ]);

        // Create a new billing record using validated data
        $billing = Billing::create($validated);

        // Return the created billing as a resource
        return new BillingResource($billing);
    }

    /**
     * Display the specified billing.
     *
     * @param \App\Models\Billing $billing

     */
    public function show(Billing $billing)
    {
        // Return the billing record as a resource
        return new BillingResource($billing);
    }
}
