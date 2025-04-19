<?php

namespace App\Rest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryNoteItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_note_id' => $this->delivery_note_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item->name ?? '',
            'quantity' => $this->quantity,
        ];
    }
}
