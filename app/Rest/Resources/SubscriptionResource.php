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
                'name' => $this->client->name,  // Assuming the 'name' field exists in the Client model
                'email' => $this->client->email, 
                'address' => $this->client->address, 
                'phone' => $this->client->phone,   // You can add other client-related fields as needed
            ],
            'plan' => [
                'id' => $this->plan_id,
                'name' => $this->plan->name,  // Assuming the 'name' field exists in the Plan model
                'description' => $this->plan->description,  // Add more plan details as necessary
                'price' => $this->plan->price,  // Assuming the 'price' field exists in the Plan model
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
