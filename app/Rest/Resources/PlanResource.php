<?php 

namespace App\Rest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'price' => $this->price,
        'duration' => $this->duration,
        'description' => $this->description,
        'provider_name' => $this->provider_name,
        'provider_price' => $this->provider_price,
        'supplier' => $this->supplier ? [  // Check if the supplier exists
            'id' => $this->supplier->id,  // Accessing supplier relationship
            'name' => $this->supplier->name,
            'email' => $this->supplier->email,
            'address' => $this->supplier->address,
        ] : null,  // If no supplier, return null
        'created_at' => $this->created_at->toDateTimeString(),
        'updated_at' => $this->updated_at->toDateTimeString(),
    ];
}

}
