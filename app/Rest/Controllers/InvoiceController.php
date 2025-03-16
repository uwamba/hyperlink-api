<?php

namespace App\Rest\Controllers;

use App\Models\Invoice;
use App\Rest\Resources\InvoiceResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
class InvoiceController extends RestController
{
    // List all invoices
    public function index()
    {
        $invoices = Invoice::all();
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
}
