<?php

namespace App\Rest\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rest\Controller as RestController;
use Carbon\Carbon;
use App\Models\Item;

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
     // Add this at the top if not already imported

     public function stockReport(Request $request)
{
    $currentYearStart = Carbon::now()->startOfYear()->toDateString();
    $currentYearEnd = Carbon::now()->endOfYear()->toDateString();

    // Validate optional date filters
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'granularity' => 'nullable|in:daily,monthly,annually',
    ]);

    $startDate = $validated['start_date'] ?? $currentYearStart;
    $endDate = $validated['end_date'] ?? $currentYearEnd;
    $granularity = $validated['granularity'] ?? 'monthly'; // Default to monthly

    // Build the base query
    $itemQuery = Item::query();

    if ($startDate && $endDate) {
        $itemQuery->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Apply grouping based on granularity
    if ($granularity === 'monthly') {
        $itemQuery->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, status, price, quantity')
                  ->groupByRaw('YEAR(created_at), MONTH(created_at), status, price, quantity');
    } elseif ($granularity === 'annually') {
        $itemQuery->selectRaw('YEAR(created_at) as year, status, price, quantity')
                  ->groupByRaw('YEAR(created_at), status, price, quantity');
    } else { // daily
        $itemQuery->selectRaw('DATE(created_at) as date, status, price, quantity')
                  ->groupByRaw('DATE(created_at), status, price, quantity');
    }

    // Fetch in-stock and delivered items separately
    $inStockItems = (clone $itemQuery)->where('status', 'in_stock')->get();
    $deliveredItems = (clone $itemQuery)->where('status', 'delivered')->get();

    // Calculate total values
    $inStockValue = $inStockItems->sum(function ($item) {
        return $item->price * $item->quantity;
    });

    $deliveredStockValue = $deliveredItems->sum(function ($item) {
        return $item->price * $item->quantity;
    });

    // Calculate total item counts
    $inStockCount = $inStockItems->count();
    $deliveredCount = $deliveredItems->count();

    // Format the data according to granularity (this is your custom function)
    $formattedData = $this->formatDataByGranularity($granularity, $inStockItems, $deliveredItems);

    return response()->json([
        'status' => 'success',
        'data' => [
            'in_stock_value' => $inStockValue,
            'delivered_stock_value' => $deliveredStockValue,
            'in_stock_count' => $inStockCount,
            'delivered_count' => $deliveredCount,
            'period_data' => $formattedData,
        ],
    ]);
}
     
     // Helper function to format data by granularity
     private function formatDataByGranularity($granularity, $inStockItems, $deliveredItems)
     {
         $formattedData = [];
     
         foreach ($inStockItems as $item) {
             $period = $this->getPeriodFromGranularity($granularity, $item);
             $formattedData[$period] = [
                 'in_stock_value' => $item->price * $item->quantity,
                 'delivered_stock_value' => $deliveredItems->sum(function ($delivered) use ($item) {
                     return $delivered->price * $delivered->quantity;
                 }),
             ];
         }
     
         return $formattedData;
     }
     
     // Helper function to get period based on granularity
     private function getPeriodFromGranularity($granularity, $item)
     {
         if ($granularity === 'monthly') {
             return $item->year . '-' . $item->month;
         } elseif ($granularity === 'annually') {
             return $item->year;
         } else {
             return $item->date; // Date format: 'YYYY-MM-DD'
         }
     }

     
     


}
