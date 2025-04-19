<?php

namespace App\Rest\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the asset resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'category'       => $this->category,
            'serial_number'  => $this->serial_number,
            'description'    => $this->description,
            'value'          => $this->value,
            'location'       => $this->location,
            'status'         => $this->status,
            'purchase_date'  => $this->purchase_date,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
