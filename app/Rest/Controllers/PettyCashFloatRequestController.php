<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;
use App\Models\PettyCashFloatRequest;
use App\Rest\Resources\PettyCashFloatRequestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FloatTransaction;
use App\Models\User;

class PettyCashFloatRequestController extends RestController
{
    /**
     * Display a listing of the float requests.
     */
    public function index()
    {
        $query = PettyCashFloatRequest::with(['user', 'approver'])->latest();

        $requests = $query->paginate(10);

        return PettyCashFloatRequestResource::collection($requests);
    }

    /**
     * Store a newly created float request.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:1',
        'reason' => 'required|string|max:255',
        'requested_for' => 'nullable|date|after_or_equal:today',
        'user_id' => 'required|exists:users,id', // Ensure user_id is valid
    ]);

    // Create the petty cash request with the provided user_id
    $floatRequest = PettyCashFloatRequest::create([
        'user_id'       => $validated['user_id'],
        'amount'        => $validated['amount'],
        'reason'        => $validated['reason'],
        'requested_for' => $validated['requested_for'] ?? null,
    ]);

    return new PettyCashFloatRequestResource($floatRequest);
}


    /**
     * Display the specified float request.
     */
    public function show(PettyCashFloatRequest $pettyCashFloatRequest)
    {

        $pettyCashFloatRequest->load(['user', 'approver']);

        return new PettyCashFloatRequestResource($pettyCashFloatRequest);
    }
    
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'approved_by' => 'required|exists:users,id',
        ]);
    
        $floatRequest = PettyCashFloatRequest::findOrFail($id);
    
        if ($floatRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed.'], 400);
        }
    
        $floatRequest->status = $request->status;
        $floatRequest->approved_by = $request->approved_by;
        $floatRequest->approved_at = now();
        $floatRequest->save();
    
        // Only create a FloatTransaction if approved
        if ($request->status === 'approved') {
            // Optional: Calculate current float balance
            $currentBalance = FloatTransaction::where('user_id', $floatRequest->user_id)
                ->orderByDesc('id')
                ->value('balance_after') ?? 0;
    
            $amount = $floatRequest->amount;
    
            FloatTransaction::create([
                'user_id' => $floatRequest->user_id,
                'amount' => $amount,
                'action' => 'added',
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance + $amount,
                'description' => 'Float approved from request ID: ' . $floatRequest->id,
            ]);
        }
    
        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => new PettyCashFloatRequestResource($floatRequest),
        ]);
    }
    


    /**
     * Approve the float request.
     */

     public function approve(Request $request, PettyCashFloatRequest $pettyCashFloatRequest)
    {
    

        if ($pettyCashFloatRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already processed.'], 400);
        }

        $pettyCashFloatRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return new PettyCashFloatRequestResource($pettyCashFloatRequest);
    }

    /**
     * Reject the float request.
     */
    public function reject(Request $request, PettyCashFloatRequest $pettyCashFloatRequest)
    {
    

        if ($pettyCashFloatRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already processed.'], 400);
        }

        $pettyCashFloatRequest->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return new PettyCashFloatRequestResource($pettyCashFloatRequest);
    }
    public function destroy($id)
     {
         $floats = PettyCashFloatRequest::findOrFail($id);
         $floats->delete();
 
         return response()->json(['message' => 'Float deleted successfully'], 200);
     }
}
