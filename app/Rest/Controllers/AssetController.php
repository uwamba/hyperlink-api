<?php

namespace App\Rest\Controllers;

use App\Models\Asset;
use App\Rest\Resources\AssetResource;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;

class AssetController extends RestController
{
    /**
     * Display a listing of the assets.
     */
    public function index()
    {
        return AssetResource::collection(Asset::all());
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|max:255',  // added category validation
            'serial_number'  => 'required|string|max:255|unique:assets,serial_number',
            'value'          => 'required|numeric|min:0',  // added value validation
            'purchase_date'  => 'required|date',  // added purchase_date validation
            'location'       => 'required|string|max:255',  // added location validation
            'status'         => 'required|string|max:255',  // added status validation
            'description'    => 'nullable|string|max:500',
        ]);

        $asset = Asset::create($data);

        return new AssetResource($asset);
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset)
    {
        return new AssetResource($asset);
    }

    /**
     * Update the specified asset in storage.
     *
     * @method PATCH
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|max:255',  // added category validation
            'serial_number'  => 'required|string|max:255|unique:assets,serial_number,' . $asset->id,
            'value'          => 'required|numeric|min:0',  // added value validation
            'purchase_date'  => 'required|date',  // added purchase_date validation
            'location'       => 'required|string|max:255',  // added location validation
            'status'         => 'required|string|max:255',  // added status validation
            'description'    => 'nullable|string|max:500',
        ]);

        $asset->update($validated);

        return new AssetResource($asset);
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json(['message' => 'Asset not found'], 404);
        }

        $asset->delete();

        return response()->json(['message' => 'Asset deleted successfully'], 200);
    }
}
