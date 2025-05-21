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
    public function inStock()
    {
        // Fetch items where status is 'in_stock'
        $items = Item::where('status', 'in_stock')->get();

        // Return the filtered items as a collection of ItemResource
        return ItemResource::collection($items);
    }
    public function outStock()
    {
        // Fetch items where status is 'in_stock'
        $items = Item::where('status', 'delivered')->get();

        // Return the filtered items as a collection of ItemResource
        return ItemResource::collection($items);
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:items,serial_number',
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'brand' => 'required|string|max:255',
        ]);

        $quantity = $data['quantity'];
        $baseSerial = $data['serial_number'];

        $items = [];

        for ($i = 1; $i <= $quantity; $i++) {
            $items[] = [
                'name' => $data['name'],
                'serial_number' => $baseSerial . '-' . $i, // make serial unique
                'description' => $data['description'],
                'quantity' => 1, // always 1
                'price' => $data['price'],
                'brand' => $data['brand'],
                'status' => 'in_stock',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all at once
        Item::insert($items);

        return response()->json(['message' => $quantity . ' items added successfully.'], 201);
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
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:items,serial_number,' . $item->id,
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'brand' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return new ItemResource($item);
    }

    /**
     * Remove the specified item from storage.
     */

    public function destroy($id)
     {
         $item = Item::findOrFail($id);
         $item->delete();

         return response()->json(['message' => '[Item deleted successfully'], 200);
     }
}
