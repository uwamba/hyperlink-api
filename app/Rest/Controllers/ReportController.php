<?php

namespace App\Rest\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rest\Controller as RestController;
use Carbon\Carbon;

class ReportController extends RestController
{
    public function salesReport(Request $request)
    {
        // Default to the start of the current year and the end of the current year
        $currentYearStart = Carbon::now()->startOfYear()->toDateString();
        $currentYearEnd = Carbon::now()->endOfYear()->toDateString();

        // Validate the input parameters
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'granularity' => 'nullable|in:daily,monthly,annually',  // Added 'annually' to validation
        ]);

        // Get the start and end dates from the request, or use the default dates
        $startDate = $validated['start_date'] ?? $currentYearStart;
        $endDate = $validated['end_date'] ?? $currentYearEnd;
        $granularity = $validated['granularity'] ?? 'annually';

        // Query the payments based on the requested date range and granularity
        $query = Payment::whereBetween('created_at', [$startDate, $endDate]);

        // Handle granularity options
        switch ($granularity) {
            case 'daily':
                $payments = $query->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount_paid) as total_income'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('date')
                    ->get();
                break;

            case 'monthly':
                $payments = $query->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('SUM(amount_paid) as total_income'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get();
                break;

            case 'annually':
                // Aggregating data by year
                $payments = $query->select(DB::raw('YEAR(created_at) as year'), DB::raw('SUM(amount_paid) as total_income'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(created_at)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;

            default:
                // If the granularity is invalid, default to annual aggregation
                $payments = $query->select(DB::raw('YEAR(created_at) as year'), DB::raw('SUM(amount_paid) as total_income'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(created_at)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;
        }

        // Return the report data in a structure usable by the frontend for charting
        if ($payments->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No data found for the specified date range.',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ]);
    }
    public function purchaseReport(Request $request)
    {
        // Default to the start of the current year and the end of the current year
        $currentYearStart = Carbon::now()->startOfYear()->toDateString();
        $currentYearEnd = Carbon::now()->endOfYear()->toDateString();

        // Validate the input parameters
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'granularity' => 'nullable|in:daily,monthly,annually',  // Added 'annually' to validation
        ]);

        // Get the start and end dates from the request, or use the default dates
        $startDate = $validated['start_date'] ?? $currentYearStart;
        $endDate = $validated['end_date'] ?? $currentYearEnd;
        $granularity = $validated['granularity'] ?? 'annually';

        // Query the purchases based on the requested date range and granularity
        $query = Purchase::whereBetween('purchase_date', [$startDate, $endDate]);

        // Handle granularity options
        switch ($granularity) {
            case 'daily':
                $purchases = $query->select(DB::raw('DATE(purchase_date) as date'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('DATE(purchase_date)'))
                    ->orderBy('date')
                    ->get();
                break;

            case 'monthly':
                $purchases = $query->select(DB::raw('MONTH(purchase_date) as month'), DB::raw('YEAR(purchase_date) as year'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(purchase_date), MONTH(purchase_date)'))
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get();
                break;

            case 'annually':
                // Aggregating data by year
                $purchases = $query->select(DB::raw('YEAR(purchase_date) as year'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(purchase_date)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;

            default:
                // If the granularity is invalid, default to annual aggregation
                $purchases = $query->select(DB::raw('YEAR(purchase_date) as year'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(purchase_date)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;
        }
        // Return the report data in a structure usable by the frontend for charting
        if ($purchases->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No data found for the specified date range.',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $purchases,
        ]);
    }

    /**
     * Generate expense report based on date range and granularity.
     */
    public function expenseReport(Request $request)
    {
        // Default to the start of the current year and the end of the current year
        $currentYearStart = Carbon::now()->startOfYear()->toDateString();
        $currentYearEnd = Carbon::now()->endOfYear()->toDateString();

        // Validate the input parameters
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'granularity' => 'nullable|in:daily,monthly,annually', // Granularity options: daily, monthly, annually
        ]);

        // Get the start and end dates from the request, or use the default dates
        $startDate = $validated['start_date'] ?? $currentYearStart;
        $endDate = $validated['end_date'] ?? $currentYearEnd;
        $granularity = $validated['granularity'] ?? 'annually';

        // Query the expenses based on the requested date range
        $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);

        // Handle granularity options
        switch ($granularity) {
            case 'daily':
                // Grouping by day
                $expenses = $query->select(DB::raw('DATE(expense_date) as date'), DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('DATE(expense_date)'))
                    ->orderBy('date')
                    ->get();
                break;

            case 'monthly':
                // Grouping by month and year
                $expenses = $query->select(DB::raw('MONTH(expense_date) as month'), DB::raw('YEAR(expense_date) as year'), DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(expense_date), MONTH(expense_date)'))
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get();
                break;

            case 'annually':
                // Grouping by year
                $expenses = $query->select(DB::raw('YEAR(expense_date) as year'), DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(expense_date)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;

            default:
                // If granularity is not defined, default to annual aggregation
                $expenses = $query->select(DB::raw('YEAR(expense_date) as year'), DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as total_transactions'))
                    ->groupBy(DB::raw('YEAR(expense_date)'))
                    ->orderBy('year', 'asc')
                    ->get();
                break;
        }

        // Return the report data in a structure usable by the frontend
        if ($expenses->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No data found for the specified date range.',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $expenses,
        ]);
    }

}
