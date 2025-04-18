<?php

namespace App\Rest\Controllers;

use App\Models\Payment;
use App\Rest\Resources\PaymentResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;

class PaymentController extends RestController
{
    // List all payments
    public function index()
    {
        $payments = Payment::all();
        return PaymentResource::collection($payments);
    }

    // Show a specific payment
    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return new PaymentResource($payment);
    }

    // Store a new payment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|uuid',
            'invoice_id' => 'required|uuid',
            'amount_paid' => 'required|numeric',
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string|unique:payments',
        ]);

        $payment = Payment::create($validated);

        // Update the invoice status
        $payment->invoice->updateStatus();

        return new PaymentResource($payment);
    }

    // Delete a payment
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully'], 200);
    }



    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // Update payment status
        $payment->status = $request->status;
        $payment->save();

        // Update invoice status accordingly
        if ($payment->invoice) {
            $invoice = $payment->invoice;

            if ($payment->status === 'approved') {
                $invoice->status = 'paid';
            } elseif ($payment->status === 'rejected') {
                $invoice->status = 'unpaid';
            }

            $invoice->save();
        }

        return response()->json(['message' => 'Payment and invoice status updated successfully.']);
    }


}
