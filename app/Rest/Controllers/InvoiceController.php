<?php

namespace App\Rest\Controllers;

use App\Models\Invoice;
use App\Rest\Resources\InvoiceResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf as pdf;


class InvoiceController extends RestController
{
    // List all invoices
    public function index()
    {
        $invoices = Invoice::all();
        return InvoiceResource::collection($invoices);
    }
    public function unpaid()
{
    // Fetch only invoices with a status of 'unpaid'
    $invoices = Invoice::where('status', 'unpaid')->get();
    return InvoiceResource::collection($invoices);
}

    // Show a specific invoice
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        return new InvoiceResource($invoice);
    }

    // Store a new invoice
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|uuid',
            'invoice_no' => 'required|string|unique:invoices',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'required|in:unpaid,paid,overdue',
        ]);

        $invoice = Invoice::create($validated);

        return new InvoiceResource($invoice);
    }

    // Update an existing invoice
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validate([
            'client_id' => 'required|uuid',
            'invoice_no' => 'required|string|unique:invoices,invoice_no,' . $id,
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'required|in:unpaid,paid,overdue',
        ]);

        $invoice->update($validated);

        return new InvoiceResource($invoice);
    }

    // Delete an invoice
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully'], 200);
    }


public function generateInvoice($subscriptionId)
{
    // Fetch the subscription with related client and plan
    $subscription = Subscription::with(['client', 'plan'])->findOrFail($subscriptionId);
    
    // Calculate amount based on the subscription's plan
    $amount = $subscription->plan->price;
    
    // Generate a unique invoice number (customize as needed)
    $invoiceNo = 'INV-' . $subscription->id . '-' . now()->format('YmdHis');
    
    // Create invoice record in the database
    $invoice = Invoice::create([
        'client_id'  => $subscription->client->id,
        'invoice_no' => $invoiceNo,
        'amount'     => $amount,
        'due_date'   => now()->addDays(30)->toDateString(),
        'status'     => 'unpaid', // default status; update later as payments are made
    ]);
    
    // Prepare the data for the invoice PDF view
    $invoiceData = [
        'invoice'    => $invoice,
        'client'     => $subscription->client,
        'plan'       => $subscription->plan,
        'amount'     => $amount,
        'start_date' => $subscription->start_date,
        'end_date'   => $subscription->end_date,
        'issue_date' => now()->toDateString(),
        'due_date'   => now()->addDays(30)->toDateString(),
    ];
    
    // Generate the PDF using the Blade view named 'invoice'
    // Adjust the PDF facade if you're using a different package (e.g., dompdf, snappy, etc.)
    $pdf = PDF::loadView('invoice', $invoiceData);
    
    // Return the PDF as a download with a name based on the invoice number
    return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');
}


}
