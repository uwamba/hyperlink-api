<?php

namespace App\Rest\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rest\Controller as RestController;

class DeliveryNoteController extends RestController
{
    /**
     * Store a newly created delivery note in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'delivery_number' => 'required|unique:delivery_notes',
            'recipient' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_note_items' => 'required|array',
            'delivery_note_items.*.item_id' => 'required|exists:items,id',
            'delivery_note_items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Start database transaction
        \DB::beginTransaction();

        try {
            // Create the delivery note
            $deliveryNote = DeliveryNote::create([
                'delivery_number' => $request->delivery_number,
                'recipient' => $request->recipient,
                'delivery_date' => $request->delivery_date,
            ]);

            // Create delivery note items
            foreach ($request->delivery_note_items as $item) {
                // Create each item record for the delivery note
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Commit the transaction
            \DB::commit();

            return response()->json([
                'message' => 'Delivery note created successfully!',
                'data' => new \App\Rest\Resources\DeliveryNoteResource($deliveryNote),
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            \DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of delivery notes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Retrieve all delivery notes with their items
            $deliveryNotes = DeliveryNote::with('items')->latest()->get();

            return response()->json([
                'success' => true,
                'data' => \App\Rest\Resources\DeliveryNoteResource::collection($deliveryNotes),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery notes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified delivery note.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $deliveryNote = DeliveryNote::with('items')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new \App\Rest\Resources\DeliveryNoteResource($deliveryNote),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery note not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified delivery note in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_note_items' => 'required|array',
            'delivery_note_items.*.item_id' => 'required|exists:items,id',
            'delivery_note_items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $deliveryNote = DeliveryNote::findOrFail($id);
            $deliveryNote->update([
                'recipient' => $request->recipient,
                'delivery_date' => $request->delivery_date,
            ]);

            // Update delivery note items
            foreach ($request->delivery_note_items as $item) {
                $deliveryNoteItem = DeliveryNoteItem::findOrFail($item['id']);
                $deliveryNoteItem->update([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json([
                'message' => 'Delivery note updated successfully!',
                'data' => new \App\Rest\Resources\DeliveryNoteResource($deliveryNote),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified delivery note from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $deliveryNote = DeliveryNote::findOrFail($id);
            $deliveryNote->delete();

            return response()->json([
                'message' => 'Delivery note deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
