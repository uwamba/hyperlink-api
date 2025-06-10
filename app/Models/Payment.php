<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id', 'invoice_id', 'amount_paid', 'payment_method', 'transaction_id','status','created_by', 'updated_by',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
