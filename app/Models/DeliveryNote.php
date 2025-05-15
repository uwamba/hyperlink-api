<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNote extends Model
{
    protected $fillable = ['delivery_number', 'recipient', 'delivery_date', 'note', 'client_id'];

    // Relationship to delivery note items
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    // Relationship to client
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

