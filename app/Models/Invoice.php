<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id', 'invoice_no', 'amount', 'due_date', 'status', 'invoice_data_type', 'invoice_data_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    

    /**
     * Update invoice status based on payments.
     */
    public function updateStatus()
    {
        $paidAmount = $this->payments->sum('amount_paid');
        if ($paidAmount >= $this->amount) {
            $this->status = 'paid';
        } elseif ($paidAmount > 0) {
            $this->status = 'unpaid';
        } else {
            $this->status = 'unpaid';
        }
        $this->save();
    }
}
