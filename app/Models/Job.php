<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'jobs';

    // The primary key associated with the table.
    protected $primaryKey = 'id';

    // Disable auto timestamps, as the table uses custom 'created_at' and other columns
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    // Cast any attributes that need conversion, e.g., 'created_at' and 'available_at' to integers
    protected $casts = [
        'available_at' => 'integer',
        'reserved_at' => 'integer',
        'created_at' => 'integer',
    ];
}
