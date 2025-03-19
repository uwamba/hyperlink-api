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
    ];

    /**
     * Get the subscriptions associated with this plan.
     */
    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }
}
