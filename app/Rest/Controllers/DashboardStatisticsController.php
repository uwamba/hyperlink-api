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
     * Get all dashboard statistics (Monthly/Annually).
     */
    public function summary(Request $request)
    {
        // Get the period type (monthly or yearly)
        $period = $request->query('period', 'monthly'); // default to monthly
        $year = $request->query('year', Carbon::now()->year); // default to current year
        $month = $request->query('month', Carbon::now()->month); // default to current month

        // Set the start and end dates for the period
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // For annual data, we change the date range accordingly
        if ($period === 'annual') {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();
        }

        try {
            // 1. Total Sales for the Period
            $totalSales = DeliveryNote::whereBetween('created_at', [$startDate, $endDate])
                                      ->sum(DB::raw('total_amount'));

            // 2. Total Purchases for the Period
            $totalPurchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
                                      ->sum(DB::raw('total_amount'));

            // 3. Total Expenses for the Period
            $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
                                    ->sum(DB::raw('amount'));

            // 4. Total Assets Value (this won't change based on period)
            $totalAssetsValue = Asset::sum(DB::raw('value'));

            // 5. Profit = Sales - Purchases - Expenses
            $profit = $totalSales - $totalPurchases - $totalExpenses;

            // 6. Total Customers Count (overall, doesn't depend on period)
            $totalCustomers = Client::count();

            // 7. Total Suppliers Count (overall, doesn't depend on period)
            $totalSuppliers = Supplier::count();

            // 8. Total Items Count (overall, doesn't depend on period)
            $totalItems = Item::count();

            // 9. Most Expensive Asset (this is also overall data)
            $mostExpensiveAsset = Asset::orderBy('value', 'desc')->first();

            // 10. Number of Assets in Each Category (grouped by category)
            $assetsByCategory = Asset::select('category', DB::raw('count(*) as count'))
                                    ->groupBy('category')
                                    ->get();

            // 11. Sales by Category for the Period (grouped by item category)
            $salesByCategory = DeliveryNote::join('delivery_note_items', 'delivery_notes.id', '=', 'delivery_note_items.delivery_note_id')
                                          ->join('items', 'delivery_note_items.item_id', '=', 'items.id')
                                          ->whereBetween('delivery_notes.created_at', [$startDate, $endDate])
                                          ->select('items.category', DB::raw('sum(delivery_note_items.quantity * delivery_note_items.unit_price) as total_sales'))
                                          ->groupBy('items.category')
                                          ->get();

            // 12. Purchases by Supplier for the Period (grouped by supplier)
            $purchasesBySupplier = Purchase::join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                                           ->whereBetween('purchases.created_at', [$startDate, $endDate])
                                           ->select('suppliers.name', DB::raw('sum(purchases.total_amount) as total_purchases'))
                                           ->groupBy('suppliers.name')
                                           ->get();

            // 13. Top 5 Products by Sales for the Period
            $topProductsBySales = DeliveryNote::join('delivery_note_items', 'delivery_notes.id', '=', 'delivery_note_items.delivery_note_id')
                                             ->join('items', 'delivery_note_items.item_id', '=', 'items.id')
                                             ->whereBetween('delivery_notes.created_at', [$startDate, $endDate])
                                             ->select('items.name', DB::raw('sum(delivery_note_items.quantity * delivery_note_items.unit_price) as total_sales'))
                                             ->groupBy('items.name')
                                             ->orderByDesc('total_sales')
                                             ->take(5)
                                             ->get();

            // 14. Expenses Breakdown by Type for the Period
            $expensesByType = Expense::select('type', DB::raw('sum(amount) as total_expenses'))
                                     ->whereBetween('created_at', [$startDate, $endDate])
                                     ->groupBy('type')
                                     ->get();

            // 15. Average Profit Margin (Total Sales / Total Purchases) for the Period
            $averageProfitMargin = $totalPurchases ? ($totalSales - $totalPurchases) / $totalSales * 100 : 0;

            // CLIENT, INVOICE, PAYMENT AND SUBSCRIPTION STATS

            // 1. Clients
            $totalClients = Client::count();
            $activeClients = Client::whereHas('activeSubscription')->count();
            $inactiveClients = Client::whereDoesntHave('activeSubscription')->count();

            // 2. Invoices
            $totalInvoices = Invoice::count();
            $unpaidInvoices = Invoice::where('status', 'unpaid')->count();

            // 3. Payments
            $totalPaymentsReceived = Payment::sum('amount_paid');

            // Stats by Date Periods
            $dailyInvoices = Invoice::whereDate('created_at', $startDate)->count();
            $dailyPayments = Payment::whereDate('created_at', $startDate)->sum('amount_paid');

            $weeklyInvoices = Invoice::whereBetween('created_at', [$startDate->startOfWeek(), $endDate->endOfWeek()])->count();
            $weeklyPayments = Payment::whereBetween('created_at', [$startDate->startOfWeek(), $endDate->endOfWeek()])->sum('amount_paid');

            $monthlyInvoices = Invoice::whereBetween('created_at', [$startDate, $endDate])->count();
            $monthlyPayments = Payment::whereBetween('created_at', [$startDate, $endDate])->sum('amount_paid');

            $annualInvoices = Invoice::whereBetween('created_at', [$startDate->startOfYear(), $endDate->endOfYear()])->count();
            $annualPayments = Payment::whereBetween('created_at', [$startDate->startOfYear(), $endDate->endOfYear()])->sum('amount_paid');

            // Subscription Stats
            $totalSubscriptions = Subscription::count();
            $activeSubscriptions = Subscription::where('status', 'active')->count();
            $expiredSubscriptions = Subscription::where('status', 'expired')->count();

            // Prepare the response data
            $data = [
                'total_sales'             => $totalSales,
                'total_purchases'         => $totalPurchases,
                'total_expenses'          => $totalExpenses,
                'total_assets_value'      => $totalAssetsValue,
                'profit'                  => $profit,
                'total_clients'           => $totalClients,
                'active_clients'          => $activeClients,
                'inactive_clients'        => $inactiveClients,
                'total_invoices'          => $totalInvoices,
                'unpaid_invoices'         => $unpaidInvoices,
                'total_payments_received' => $totalPaymentsReceived,
                'daily_invoices'          => $dailyInvoices,
                'daily_payments'          => $dailyPayments,
                'weekly_invoices'         => $weeklyInvoices,
                'weekly_payments'         => $weeklyPayments,
                'monthly_invoices'        => $monthlyInvoices,
                'monthly_payments'        => $monthlyPayments,
                'annual_invoices'         => $annualInvoices,
                'annual_payments'         => $annualPayments,
                'total_subscriptions'     => $totalSubscriptions,
                'active_subscriptions'    => $activeSubscriptions,
                'expired_subscriptions'   => $expiredSubscriptions,
                'total_customers'         => $totalCustomers,
                'total_suppliers'         => $totalSuppliers,
                'total_items'             => $totalItems,
                'most_expensive_asset'    => $mostExpensiveAsset,
                'assets_by_category'      => $assetsByCategory,
                'sales_by_category'       => $salesByCategory,
                'purchases_by_supplier'   => $purchasesBySupplier,
                'top_products_by_sales'   => $topProductsBySales,
                'expenses_by_type'        => $expensesByType,
                'average_profit_margin'   => $averageProfitMargin,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
