<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Define the table name if it's different from the plural form of the model name
    protected $table = 'products';

    // Define the fillable fields (mass assignable)
    protected $fillable = [
        'name',
        'description',
        'brand'
    ];   //
}
