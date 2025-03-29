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
    public function paid()
    {
        // Fetch only invoices with a status of 'unpaid'
        $invoices = Invoice::where('status', 'paid')->get();
        return InvoiceResource::collection($invoices);
    }
    public function overdue()
    {
        // Fetch only invoices with a status of 'unpaid'
        $invoices = Invoice::where('status', 'overdue')->get();
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

    // Check if an invoice was already generated for this subscription in the current month
    $existingInvoice = Invoice::where('client_id', $subscription->client->id)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->first();

    if ($existingInvoice) {
        // If an invoice already exists for the current month, return the existing one
        $invoiceData = [
            'invoice' => $existingInvoice,
            'client' => $subscription->client,
            'plan' => $subscription->plan,
            'amount' => $existingInvoice->amount,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'issue_date' => $existingInvoice->created_at->toDateString(),
            'due_date' => $existingInvoice->due_date,
        ];

        $pdf = PDF::loadView('invoice', $invoiceData);

        return $pdf->download('invoice_' . $existingInvoice->invoice_no . '.pdf');
    }

    // Calculate amount based on the subscription's plan
    $amount = $subscription->plan->price;

    // Generate a unique invoice number
    $invoiceNo = 'INV-' . $subscription->id . '-' . now()->format('Ym');

    // Create a new invoice record
    $invoice = Invoice::create([
        'client_id' => $subscription->client->id,
        'invoice_no' => $invoiceNo,
        'amount' => $amount,
        'due_date' => now()->addDays(30)->toDateString(),
        'status' => 'unpaid',
    ]);

    // Prepare invoice data for the PDF
    $invoiceData = [
        'invoice' => $invoice,
        'client' => $subscription->client,
        'plan' => $subscription->plan,
        'amount' => $amount,
        'start_date' => $subscription->start_date,
        'end_date' => $subscription->end_date,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
    ];

    $pdf = PDF::loadView('invoice', $invoiceData);

    return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');
}

public function downloadInvoice($invoiceId)
{
    // Fetch the invoice along with the related client and subscription plan
    $invoice = Invoice::with(['client'])->findOrFail($invoiceId);

    // Retrieve the subscription related to the invoice
    $subscription = Subscription::where('client_id', $invoice->client_id)->firstOrFail();

    // Prepare invoice data for the PDF
    $invoiceData = [
        'invoice' => $invoice,
        'client' => $invoice->client,
        'plan' => $subscription->plan,
        'amount' => $invoice->amount,
        'start_date' => $subscription->start_date,
        'end_date' => $subscription->end_date,
        'issue_date' => $invoice->created_at->toDateString(),
        'due_date' => $invoice->due_date,
    ];

    // Generate the invoice PDF
    $pdf = PDF::loadView('invoice', $invoiceData);

    // Return the PDF as a download with a formatted file name
    return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');
}



}
