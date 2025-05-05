<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloatTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'action',
        'balance_before',
        'balance_after',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
