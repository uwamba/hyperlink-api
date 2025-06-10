<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier',
        'invoice_number',
        'purchase_date',
        'total_amount',
        'note',
        'created_by', 'updated_by',
    ];
}
