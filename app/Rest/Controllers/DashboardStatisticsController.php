<?php

namespace App\Rest\Controllers;

use App\Models\Asset;
use App\Models\DeliveryNote;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardStatisticsController extends RestController
{
    /**
     * Get all dashboard statistics (Monthly/Annually) with dynamic date ranges.
     */
    public function index(Request $request)
{
    // Default start and end dates for testing
    $defaultStartDate = Carbon::create(2025, 1, 1); // January 1, 2025
    $defaultEndDate = Carbon::create(2025, 5, 1);   // May 1, 2025

    // Validate and get the custom date ranges from the request (if provided)
    $startDate = $request->input('start_date', $defaultStartDate);
    $endDate = $request->input('end_date', $defaultEndDate);

    // If custom date ranges are provided, parse them
    if ($startDate && $endDate) {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
    }

    // Log the date range to check if the default values are used
    \Log::info('Using Date Range - Start Date: ' . $startDate->toDateString() . ', End Date: ' . $endDate->toDateString());

    // Helper function to calculate metrics for a date range
    $calculateMetrics = function ($startDate, $endDate) {
        // Sales, Purchases, Expenses, Profit Calculation
        $totalSales = Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->where('invoices.status', 'paid')
            ->sum('payments.amount_paid');

        $totalPurchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('total_amount'));

        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('amount'));

        $profit = $totalSales - $totalPurchases - $totalExpenses;

        $averageProfitMargin = ($totalSales > 0)
            ? (($totalSales - $totalPurchases) / $totalSales) * 100
            : 0;

        // Expense data by type
        $expensesByType = Expense::select('category', DB::raw('sum(amount) as total_expenses'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('category')
            ->get();

        // Purchases data by supplier
        $purchasesBySupplier = Purchase::join('suppliers', 'purchases.supplier', '=', 'suppliers.id')
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->select('suppliers.name', DB::raw('sum(purchases.total_amount) as total_purchases'))
            ->groupBy('suppliers.name')
            ->get();

        return [
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'average_profit_margin' => $averageProfitMargin,
            'expenses_by_type' => $expensesByType,
            'purchases_by_supplier' => $purchasesBySupplier,
        ];
    };

    // Get data for each period (using custom date ranges or defaults)
    $weeklyMetrics = $calculateMetrics($startDate->copy()->startOfWeek(Carbon::MONDAY), $endDate->copy()->endOfWeek(Carbon::SUNDAY));
    $monthlyMetrics = $calculateMetrics($startDate->copy()->startOfMonth(), $endDate->copy()->endOfMonth());
    $annualMetrics = $calculateMetrics($startDate->copy()->startOfYear(), $endDate->copy()->endOfYear());

    // General (period-independent) data
    $generalData = [
        'total_assets_value' => Asset::sum(DB::raw('value')),
        'total_clients' => Client::count(),
        // Get active clients by checking if they have an active subscription
        'active_clients' => Client::whereHas('activeSubscription', function ($query) {
            $query->where('status', 'active');
        })->count(),
        // Inactive Clients
        'inactive_clients' => Client::whereDoesntHave('activeSubscription', function ($query) {
            $query->where('status', 'active');
        })->count(),
        'total_suppliers' => Supplier::count(),
        'total_items' => Item::count(),
        'most_expensive_asset' => Asset::orderBy('value', 'desc')->first(),
        'assets_by_category' => Asset::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get(),
        'total_subscriptions' => Subscription::count(),
        'active_subscriptions' => Subscription::where('status', 'active')->count(),
        'expired_subscriptions' => Subscription::where('status', 'expired')->count(),
        'total_invoices' => Invoice::count(),
        'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
        'total_payments_received' => Payment::sum('amount_paid'),
    ];

    return response()->json([
        'weekly' => $weeklyMetrics,
        'monthly' => $monthlyMetrics,
        'annual' => $annualMetrics,
        'general' => $generalData,
    ]);
}

}
