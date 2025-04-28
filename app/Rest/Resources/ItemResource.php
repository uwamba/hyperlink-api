<?php

namespace App\Rest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'serial_number'  => $this->serial_number,
            'description'    => $this->description,
            'quantity'       => $this->quantity,
            'price'          => $this->price,
            'brand'          => $this->brand,
            'status'         => $this->status,
            'client_id'      => $this->client_id,
            'delivered_at'   => $this->delivered_at?->toDateTimeString(),
            'client'         => $this->whenLoaded('client', function () {
                return [
                    'id'   => $this->client->id,
                    'name' => $this->client->name,
                ];
            }),
            'created_at'     => $this->created_at?->toDateTimeString(),
            'updated_at'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
