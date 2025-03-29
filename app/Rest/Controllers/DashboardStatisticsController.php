<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;
use App\Models\Client;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Carbon\Carbon;

class DashboardStatisticsController extends RestController
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // Clients

        $totalClients = Client::count();

        // Count active clients with an active subscription
        $activeClients = Client::whereHas('activeSubscription')->count();

        // Count inactive clients without an active subscription
        $inactiveClients = Client::whereDoesntHave('activeSubscription')->count();

        // Invoices
        $totalInvoices = Invoice::count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();

        // Payments
        $totalPaymentsReceived = Payment::sum('amount_paid');

        // Daily Stats
        $dailyInvoices = Invoice::whereDate('created_at', $today)->count();
        $dailyPayments = Payment::whereDate('created_at', $today)->sum('amount_paid');

        // Weekly Stats
        $weeklyInvoices = Invoice::whereBetween('created_at', [$startOfWeek, Carbon::now()])->count();
        $weeklyPayments = Payment::whereBetween('created_at', [$startOfWeek, Carbon::now()])->sum('amount_paid');

        // Monthly Stats
        $monthlyInvoices = Invoice::whereBetween('created_at', [$startOfMonth, Carbon::now()])->count();
        $monthlyPayments = Payment::whereBetween('created_at', [$startOfMonth, Carbon::now()])->sum('amount_paid');

        // Annual Stats
        $annualInvoices = Invoice::whereBetween('created_at', [$startOfYear, Carbon::now()])->count();
        $annualPayments = Payment::whereBetween('created_at', [$startOfYear, Carbon::now()])->sum('amount_paid');

        // Subscription Stats
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')->count();

        return response()->json([
            'total_clients' => $totalClients,
            'active_clients' => $activeClients,
            'inactive_clients' => $inactiveClients,
            'total_invoices' => $totalInvoices,
            'unpaid_invoices' => $unpaidInvoices,
            'total_payments_received' => $totalPaymentsReceived,
            'daily_invoices' => $dailyInvoices,
            'daily_payments' => $dailyPayments,
            'weekly_invoices' => $weeklyInvoices,
            'weekly_payments' => $weeklyPayments,
            'monthly_invoices' => $monthlyInvoices,
            'monthly_payments' => $monthlyPayments,
            'annual_invoices' => $annualInvoices,
            'annual_payments' => $annualPayments,
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
        ]);
    }
}
