<?php

namespace App\Rest\Controllers;

use App\Models\Expense;
use App\Rest\Resources\ExpenseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Models\FloatTransaction;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends RestController
{
    /**
     * Display a listing of the expenses.
     */
    public function index()
    {
        //return ExpenseResource::collection(Expense::all());
        return ExpenseResource::collection(Expense::orderBy('created_at', 'desc')->get());

    }

    /**
     * Store a newly created expense in storage.
     */

    
    public function store(Request $request)
    {
        // Validate incoming request data
        $data = $request->validate([
            'description'  => 'required|string|max:500',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category'     => 'nullable|string|max:255',
        ]);
    
        // Attach the currently authenticated user (or pass via request if needed)
        $data['user_id'] = Auth::id(); // Or $request->input('user_id') if not using Auth
    
        // Create a new expense
        $expense = Expense::create($data);
    
        // Get current balance before transaction
        $currentBalance = FloatTransaction::where('user_id', $data['user_id'])
            ->orderByDesc('id')
            ->value('balance_after') ?? 0;
    
        // Subtract the expense from the float
        FloatTransaction::create([
            'user_id'        => $data['user_id'],
            'amount'         => $data['amount'],
            'action'         => 'expensed',
            'balance_before' => $currentBalance,
            'balance_after'  => $currentBalance - $data['amount'],
            'description'    => 'Expense: ' . $data['description'],
        ]);
    
        // Return the created expense as a resource
        return new ExpenseResource($expense);
    }
    

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        // Return the expense as a resource
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified expense in storage.
     *
     * @method PATCH
     */
    public function update(Request $request, Expense $expense)
{
    // ✅ Validate incoming request data
    $validated = $request->validate([
        'description'  => 'required|string|max:500',
        'amount'       => 'required|numeric|min:0',
        'expense_date' => 'required|date',
        'category'     => 'nullable|string|max:255',
    ]);

    // ✅ Get the logged-in user
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // ✅ Get the user's latest float transaction
    $lastTransaction = FloatTransaction::where('user_id', $user->id)
        ->latest()
        ->first();

    $currentBalance = $lastTransaction ? $lastTransaction->balance_after : 0;

    // ✅ Find difference between old and new amount
    $oldAmount = $expense->amount;
    $newAmount = $validated['amount'];
    $difference = $oldAmount - $newAmount; // positive means refund to float, negative means deduction

    // ✅ Compute the new balance
    $newBalance = $currentBalance + $difference;

    // ✅ Record a float transaction for this adjustment
    FloatTransaction::create([
        'user_id' => $user->id,
        'amount' => abs($difference),
        'action' => 'expensed',
        'balance_before' => $currentBalance,
        'balance_after' => $newBalance,
        'description' => "Adjustment for updated expense: {$expense->description}",
    ]);

    // ✅ Update the expense record
    $expense->update($validated);

    // ✅ Return response
    return response()->json([
        'message' => 'Expense updated successfully',
        'expense' => $expense,
        'new_balance' => $newBalance,
    ], 200);
}

    /**
     * Remove the specified expense from storage.
     */
 public function destroy($id)
{
    // Find the expense by ID
    $expense = Expense::find($id);

    if (!$expense) {
        return response()->json(['message' => 'Expense not found'], 404);
    }

    // ✅ Assume the logged-in user made the expense
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // ✅ Get user's last float transaction to know their current balance
    $lastTransaction = FloatTransaction::where('user_id', $user->id)
        ->latest()
        ->first();

    $currentBalance = $lastTransaction ? $lastTransaction->balance_after : 0;

    // ✅ Calculate new balance after deleting expense
    $newBalance = $currentBalance + $expense->amount;

    // ✅ Record the float transaction for traceability
    FloatTransaction::create([
        'user_id' => $user->id,
        'amount' => $expense->amount,
        'action' => 'expensed',
        'balance_before' => $currentBalance,
        'balance_after' => $newBalance,
        'description' => 'Reversal of deleted expense: ' . $expense->description,
    ]);

    // ✅ Delete the expense
    $expense->delete();

    return response()->json([
        'message' => 'Expense deleted successfully',
        'new_balance' => $newBalance,
    ], 200);
}

}
