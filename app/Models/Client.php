<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends Model {
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get the subscription associated with the client.
     */
    
    public function billings() {
        return $this->hasMany(Billing::class);
    }

    /**
     * Get the plan associated with the client's current subscription.
     */
    public function plan() {
        return $this->hasOneThrough(
            Plan::class,
            Subscription::class,
            'client_id', // Foreign key on subscriptions table
            'id', // Foreign key on plans table
            'id', // Local key on clients table
            'plan_id' // Local key on subscriptions table
        );
    }

    /**
     * Get the subscription history for the client (if you want to track past subscriptions).
     */
    public function subscriptionHistory() {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription, if any (if you only want one active subscription).
     */
    public function activeSubscription() {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }
}
