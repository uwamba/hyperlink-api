<?php

namespace App\Rest\Controllers;

use App\Models\Invoice;
use App\Rest\Resources\InvoiceResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf as pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\DeliveryNote;


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
        $invoices = Invoice::where('status', 'unpaid')
            ->orderBy('created_at', 'desc')
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function paid()
    {
        $invoices = Invoice::where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function overdue()
    {
        $invoices = Invoice::where('status', 'overdue')
            ->orderBy('created_at', 'desc')
            ->get();

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



    public function downloadInvoice($invoiceId)
    {
        $invoice = Invoice::with(['client'])->findOrFail($invoiceId);
        Log::info("Invoice found", ['invoice' => $invoice]);

        // Common invoice fields
        $invoiceData = [
            'invoice' => $invoice,
            'client' => $invoice->client,
            'amount' => $invoice->amount,
            'issue_date' => $invoice->created_at->toDateString(),
            'due_date' => $invoice->due_date,
            'BANK_ACCOUNT_NAME' => env('BANK_ACCOUNT_NAME', 'Your Company Name'),
            'BANK_ACCOUNT_RWF' => env('BANK_ACCOUNT_RWF', '1234567890'),
            'BANK_ACCOUNT_USD' => env('BANK_ACCOUNT_USD', '0987654321'),
            'COMPANY_NAME' => env('COMPANY_NAME', 'Your Company Name Ltd'),
            'COMPANY_TIN' => env('COMPANY_TIN', '123456789'),
        ];

        if ($invoice->invoice_data_type === 'items') {
            // Fetch delivery note by invoice_data_id
            $deliveryNote = DeliveryNote::with('items')->find($invoice->invoice_data_id);

            if (!$deliveryNote) {
                Log::warning("Delivery note not found", ['invoice_data_id' => $invoice->invoice_data_id]);
                abort(404, 'Delivery note not found for invoice.');
            }

            Log::info("Delivery note with items found", ['delivery_note' => $deliveryNote]);

            $invoiceData['delivery_note'] = $deliveryNote;
            $invoiceData['items'] = $deliveryNote->items;

        } elseif ($invoice->invoice_data_type === 'subscription') {
            // Fetch subscription with plan using invoice_data_id
            $subscription = Subscription::with('plan')->find($invoice->invoice_data_id);

            if (!$subscription) {
                Log::warning("Subscription not found", ['invoice_data_id' => $invoice->invoice_data_id]);
                abort(404, 'Subscription not found for invoice.');
            }

            Log::info("Subscription with plan found", ['subscription' => $subscription]);

            $invoiceData['plan'] = $subscription->plan;

        } else {
            Log::error("Unknown invoice data type", ['type' => $invoice->invoice_data_type]);
            abort(400, 'Invalid invoice data type');
        }

        // Generate the PDF
        $template = $invoice->invoice_data_type === 'items' ? 'invoice_from_deliveryNote' : 'manualInvoice';
        $pdf = PDF::loadView($template, $invoiceData);

        return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');
    }



    public function generatePDFInvoice($invoiceId)
    {
        $invoice = Invoice::with(['client'])->findOrFail($invoiceId);
        Log::info("Invoice found", ['invoice' => $invoice]);

        // Common invoice fields
        $invoiceData = [
            'invoice' => $invoice,
            'client' => $invoice->client,
            'amount' => $invoice->amount,
            'issue_date' => $invoice->created_at->toDateString(),
            'due_date' => $invoice->due_date,
            'BANK_ACCOUNT_NAME' => env('BANK_ACCOUNT_NAME', 'Your Company Name'),
            'BANK_ACCOUNT_RWF' => env('BANK_ACCOUNT_RWF', '1234567890'),
            'BANK_ACCOUNT_USD' => env('BANK_ACCOUNT_USD', '0987654321'),
            'COMPANY_NAME' => env('COMPANY_NAME', 'Your Company Name Ltd'),
            'COMPANY_TIN' => env('COMPANY_TIN', '123456789'),
        ];


        Log::info("Invoice created for Subscription ID: {$invoiceId}");



        $subscription = Subscription::with('plan')->find($invoice->invoice_data_id);

        if (!$subscription) {
            Log::warning("Subscription not found", ['invoice_data_id' => $invoice->invoice_data_id]);
            abort(404, 'Subscription not found for invoice.');
        }

        Log::info("Subscription with plan found", ['subscription' => $subscription]);

        $invoiceData['plan'] = $subscription->plan;




        // Generate the PDF using the invoice data
        $pdf = PDF::loadView('manualInvoice', $invoiceData);
        return $pdf->download('invoice_' . $invoice->invoice_no . '.pdf');



    }



}
