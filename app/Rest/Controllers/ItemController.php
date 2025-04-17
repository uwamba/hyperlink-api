<?php

namespace App\Rest\Controllers;

use App\Models\Item;
use App\Rest\Resources\ItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;

class ItemController extends RestController
{
    /**
     * Display a listing of the items.
     */
    public function index()
    {
        return ItemResource::collection(Item::all());
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'serial_number'  => 'required|string|max:255|unique:items,serial_number',
            'description'    => 'nullable|string|max:500',
            'quantity'       => 'required|integer|min:0',
            'price'          => 'required|numeric|min:0',
            'brand'          => 'required|string|max:255',
        ]);

        $item = Item::create($data);

        return new ItemResource($item);
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item)
    {
        return new ItemResource($item);
    }

    /**
     * Update the specified product in storage.
     *
     * @method PATCH
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'serial_number'  => 'required|string|max:255|unique:items,serial_number,' . $item->id,
            'description'    => 'nullable|string|max:500',
            'quantity'       => 'required|integer|min:0',
            'price'          => 'required|numeric|min:0',
            'brand'          => 'required|string|max:255',
        ]);

        $item->update($validated);

        return new ItemResource($item);
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }
}
