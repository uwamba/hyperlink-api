<?php

namespace App\Rest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_number' => $this->delivery_number,
            'delivery_date' => $this->delivery_date, // Changed from 'date' to 'delivery_date'
            'recipient' => $this->recipient,
            'items' => DeliveryNoteItemResource::collection($this->whenLoaded('items')), // Assumes 'items' relation is loaded
            'created_at' => $this->created_at,
        ];
    }
}
