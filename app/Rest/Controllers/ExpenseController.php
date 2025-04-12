<?php

namespace App\Rest\Controllers;

use App\Models\Expense;
use App\Rest\Resources\ExpenseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;

class ExpenseController extends RestController
{
    /**
     * Display a listing of the expenses.
     */
    public function index()
    {
        return ExpenseResource::collection(Expense::all());
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

        // Create a new expense
        $expense = Expense::create($data);

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
        // Validate incoming request data
        $validated = $request->validate([
            'description'  => 'required|string|max:500',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category'     => 'nullable|string|max:255',
        ]);

        // Update the expense with validated data
        $expense->update($validated);

        // Return the updated expense as a resource
        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy($expense)
    {
        // Find the expense by ID
        $expense = Expense::find($expense);

        if (!$expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        // Delete the expense
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully'], 200);
    }
}
