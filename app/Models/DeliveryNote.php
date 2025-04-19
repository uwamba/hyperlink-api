<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    protected $fillable = ['delivery_number', 'recipient', 'delivery_date', 'note'];

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}
