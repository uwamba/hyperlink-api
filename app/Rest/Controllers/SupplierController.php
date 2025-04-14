<?php

namespace App\Rest\Controllers;

use App\Models\Supplier;
use App\Rest\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;

class SupplierController extends RestController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SupplierResource::collection(Supplier::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:suppliers,email',
        ]);

        $supplier = Supplier::create($data);
        return new SupplierResource($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:clients,email',
        ]);


        $supplier->update($validated);
        return new SupplierResource($supplier);
    }
    public function destroy($supplier)
    {
        $support = Supplier::find($supplier);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier request not found'], 404);
        }

        $supplier->delete();


        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }
}
