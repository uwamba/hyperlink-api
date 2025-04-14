<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Plan extends Model {
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'price',
        'duration',
        'description',
        'provider_name',
        'supplier_id',
        'provider_price',
    ];

    /**
     * Get the subscriptions associated with this plan.
     */
    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }
    public function supplier()
{
    return $this->belongsTo(Supplier::class);
}

    /**
     * Get the plan's price in a formatted way.
     */
    public function getFormattedPriceAttribute() {
        return number_format($this->price, 2);
    }

    /**
     * Get the plan's duration in a formatted way.
     */
    public function getFormattedDurationAttribute() {
        return $this->duration . ' days';
    }
}
