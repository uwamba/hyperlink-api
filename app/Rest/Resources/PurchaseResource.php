<?php
namespace App\Rest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier' => $this->supplier,
            'invoice_number' => $this->invoice_number,
            'purchase_date' => $this->purchase_date,
            'total_amount' => $this->total_amount,
            'note' => $this->note,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
