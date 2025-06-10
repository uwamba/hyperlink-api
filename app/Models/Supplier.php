<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';
        protected $fillable = [
        'name',
        'address',
        'telephone',
        'email',
        'created_by', 'updated_by',
    ];

    /**
     * Define relationships here if needed.
     */
}

