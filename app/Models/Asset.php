<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'serial_number',
        'value',
        'purchase_date',
        'location',
        'status',
        'description', // ✅ include description
        'created_by', 'updated_by',
    ];
}
