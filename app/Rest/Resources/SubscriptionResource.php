<?php

namespace App\Rest\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'client' => [
                'id' => $this->client_id,
                'name' => $this->client->name,
                'email' => $this->client->email,
                'address' => $this->client->address,
                'phone' => $this->client->phone,
            ],
            'plan' => [
                'id' => $this->plan_id,
                'name' => $this->plan->name,
                'description' => $this->plan->description,
                'price' => $this->plan->price,
                'provider_name' => $this->plan->provider_name,
                'provider_price' => $this->plan->provider_price,
                'supplier' => $this->whenLoaded('plan.supplier', function () {
                    return [
                        'id' => $this->plan->supplier->id,
                        'name' => $this->plan->supplier->name,
                        'email' => $this->plan->supplier->email ?? null,
                        // Add more supplier fields if needed
                    ];
                }),
            ],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'billing_date' => $this->billing_date,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->updated_at->toDateString(),
        ];
    }
}
