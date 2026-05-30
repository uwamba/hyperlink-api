<?php

namespace App\Rest\Controllers;

use App\Models\Payment;
use App\Rest\Resources\PaymentResource;
use App\Services\PaymentProofService;
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

    // Store a new payment — requires proof attachment
    public function store(Request $request)
    {
        $request->validate([
            'client_id'      => 'required|uuid',
            'invoice_id'     => 'required|uuid',
            'amount_paid'    => 'required|numeric',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|unique:payments',
            'proof'          => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ]);

        $payment = Payment::create([
            'client_id'      => $request->client_id,
            'invoice_id'     => $request->invoice_id,
            'amount_paid'    => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?? 'TXN-' . time(),
            'status'         => 'completed',
            'created_by'     => auth()->id() ?? null,
        ]);

        // Store proof file
        $proofService = new PaymentProofService();
        $proof        = $proofService->store($request->file('proof'), $payment->id);

        // Update invoice status
        $payment->invoice->updateStatus();

        return response()->json([
            'message'   => 'Payment recorded successfully.',
            'payment'   => new PaymentResource($payment),
            'proof_url' => $proof['url'],
        ], 201);
    }

    // Delete a payment
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully'], 200);
    }

    // Update payment status (approve/reject)
    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $payment->status = $request->status;
        $payment->save();

        if ($payment->invoice) {
            $invoice = $payment->invoice;
            $invoice->status = $payment->status === 'approved' ? 'paid' : 'unpaid';
            $invoice->save();
        }

        return response()->json(['message' => 'Payment and invoice status updated successfully.']);
    }

    // Get proof for a specific payment
    public function getProof(string $id)
    {
        $service = new PaymentProofService();
        $proof   = $service->getByPaymentId($id);

        if (!$proof) {
            return response()->json(['message' => 'No proof found for this payment.'], 404);
        }

        return response()->json(['proof' => $proof]);
    }
}