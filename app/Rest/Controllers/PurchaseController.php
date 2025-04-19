<?php

namespace App\Rest\Controllers;

use App\Models\Purchase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rest\Resources\PurchaseResource;

class PurchaseController extends Controller
{
    public function index()
    {
        return PurchaseResource::collection(Purchase::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        $purchase = Purchase::create($data);

        return new PurchaseResource($purchase);
    }

    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'supplier' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        $purchase->update($data);

        return new PurchaseResource($purchase);
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}
