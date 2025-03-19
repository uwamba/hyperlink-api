<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Billing extends Model {
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id',
        'invoice_id',
        'amount',
        'due_date',
        'status',
    ];

    /**
     * Get the client related to this billing.
     */
    public function client() {
        return $this->belongsTo(Client::class);
    }
}
