<?php

namespace App\Rest\Controllers;

use Illuminate\Http\Request;
use App\Rest\Controller as RestController;

use App\Models\User;
use App\Models\Client;
use App\Models\Support;
use App\Models\Payment;
use App\Models\Item;
use App\Models\DeliveryNote;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;

class UserPerformanceController extends RestController
{
    public function index(): JsonResponse
    {
        $users = User::all();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->id,
                'user_name' => $user->email,
                'clients_created_this_month' => Client::where('created_by', $user->id)
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),

                'clients_updated_this_month' => Client::where('updated_by', $user->id)
                    ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),

                'tickets_created_this_month' => Support::where('created_by', $user->id)
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),

                'tickets_updated_this_month' => Support::where('updated_by', $user->id)
                    ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function getMonthRange()
    {
        return [now()->startOfMonth(), now()->endOfMonth()];
    }

    public function performancePayments(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = Payment::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($payments, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'created_this_month' => $payments->whereBetween('created_at', [$start, $end])->count(),
                    'updated_this_month' => $payments->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function items(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = Item::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'items_created_this_month' => $items->whereBetween('created_at', [$start, $end])->count(),
                    'items_updated_this_month' => $items->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function deliveryNotes(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = DeliveryNote::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($notes, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'created_this_month' => $notes->whereBetween('created_at', [$start, $end])->count(),
                    'updated_this_month' => $notes->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function subscriptions(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = Subscription::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($subs, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'created_this_month' => $subs->whereBetween('created_at', [$start, $end])->count(),
                    'updated_this_month' => $subs->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function invoices(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        // Example: updating overdue invoices to paid is a separate operation, this endpoint just reports counts

        $data = Invoice::whereBetween('updated_at', [$start, $end])
            ->where('status', 'paid')
            ->get()
            ->groupBy('user_id')
            ->map(function ($invoices, $userId) {
                return [
                    'user_id' => $userId,
                    'invoices_paid_this_month' => $invoices->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function purchases(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = Purchase::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($purchases, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'purchases_created_this_month' => $purchases->whereBetween('created_at', [$start, $end])->count(),
                    'purchases_updated_this_month' => $purchases->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function assets(): JsonResponse
    {
        [$start, $end] = $this->getMonthRange();

        $data = Asset::whereBetween('created_at', [$start, $end])
            ->orWhereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(function ($assets, $userId) use ($start, $end) {
                return [
                    'user_id' => $userId,
                    'assets_created_this_month' => $assets->whereBetween('created_at', [$start, $end])->count(),
                    'assets_updated_this_month' => $assets->whereBetween('updated_at', [$start, $end])->count(),
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }
}
