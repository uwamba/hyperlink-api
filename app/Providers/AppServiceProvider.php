<?php

namespace App\Providers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Supplier;
use Illuminate\Support\ServiceProvider;
use App\Observers\TrackUserObserver;
use App\Models\Asset;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Product;
use App\Models\PettyCashFloatRequest;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Purchase;
use App\Models\Support;
use App\Models\User;





class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Asset::observe(TrackUserObserver::class);
        Client::observe(TrackUserObserver::class);
        Supplier::observe(TrackUserObserver::class);
        Billing::observe(TrackUserObserver::class);
        Asset::observe(TrackUserObserver::class);
        DeliveryNote::observe(TrackUserObserver::class);
        DeliveryNoteItem::observe(TrackUserObserver::class);
        Expense::observe(TrackUserObserver::class);
        Invoice::observe(TrackUserObserver::class);
        Item::observe(TrackUserObserver::class);
        Payment::observe(TrackUserObserver::class);
        PettyCashFloatRequest::observe(TrackUserObserver::class);
        Plan::observe(TrackUserObserver::class);
        Product::observe(TrackUserObserver::class);
        Subscription::observe(TrackUserObserver::class);
        Purchase::observe(TrackUserObserver::class);
        Supplier::observe(TrackUserObserver::class);
        Support::observe(TrackUserObserver::class);
        User::observe(TrackUserObserver::class);


    }
}
