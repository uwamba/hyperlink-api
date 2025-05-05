<?php

namespace App\Rest\Controllers;

use App\Models\FloatTransaction;
use App\Rest\Resources\FloatTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rest\Controller as RestController;

class FloatTransactionController extends RestController
{
    public function index()
    {
        $transactions = FloatTransaction::with('user')->latest()->paginate(10);
        return FloatTransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'action' => 'required|in:added,expensed',
            'balance_before' => 'required|numeric',
            'balance_after' => 'required|numeric',
            'description' => 'nullable|string|max:1000',
        ]);

        $transaction = FloatTransaction::create($validated);

        return new FloatTransactionResource($transaction);
    }

    public function show(FloatTransaction $floatTransaction)
    {
        $floatTransaction->load('user');
        return new FloatTransactionResource($floatTransaction);
    }

    public function destroy($floatTransaction)
    {
        $floatTransaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }
}
