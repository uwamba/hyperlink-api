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
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $createdCount = Payment::where('created_by', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $updatedCount = Payment::where('updated_by', $user->id)
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email, // or use name/username if preferred
            'created_this_month' => $createdCount,
            'updated_this_month' => $updatedCount,
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}

    public function items(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $createdCount = Item::where('created_by', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $updatedCount = Item::where('updated_by', $user->id)
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'items_created_this_month' => $createdCount,
            'items_updated_this_month' => $updatedCount,
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}


    public function deliveryNotes(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'created_this_month' => DeliveryNote::where('created_by', $user->id)->whereBetween('created_at', [$start, $end])->count(),
            'updated_this_month' => DeliveryNote::where('updated_by', $user->id)->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}

public function subscriptions(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'created_this_month' => Subscription::where('created_by', $user->id)->whereBetween('created_at', [$start, $end])->count(),
            'updated_this_month' => Subscription::where('updated_by', $user->id)->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}

public function invoices(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'invoices_paid_this_month' => Invoice::where('updated_by', $user->id)
                ->where('status', 'paid')
                ->whereBetween('updated_at', [$start, $end])
                ->count(),
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}

public function purchases(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'purchases_created_this_month' => Purchase::where('created_by', $user->id)->whereBetween('created_at', [$start, $end])->count(),
            'purchases_updated_this_month' => Purchase::where('updated_by', $user->id)->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}

public function assets(): JsonResponse
{
    [$start, $end] = $this->getMonthRange();
    $users = User::all();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'user_id' => $user->id,
            'user_name' => $user->email,
            'assets_created_this_month' => Asset::where('created_by', $user->id)->whereBetween('created_at', [$start, $end])->count(),
            'assets_updated_this_month' => Asset::where('updated_by', $user->id)->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    return response()->json(['success' => true, 'data' => $data]);
}
}
