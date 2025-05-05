<?php

namespace App\Rest\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FloatTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'amount' => $this->amount,
            'action' => $this->action,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
