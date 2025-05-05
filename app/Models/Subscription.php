<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model {
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'contract',
        'billing_date',
        
    ];

    /**
     * Get the client who owns this subscription.
     */
    public function client() {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the plan that this subscription is associated with.
     */
    public function plan() {
        return $this->belongsTo(Plan::class);
    }
}
