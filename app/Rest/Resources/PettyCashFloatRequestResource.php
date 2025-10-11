<?php

// app/Rest/Resources/PettyCashFloatRequestResource.php

namespace App\Rest\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FloatTransaction;

class PettyCashFloatRequestResource extends JsonResource
{
    /**
     * Transform the petty cash float request resource into an array.
     */


public function toArray($request): array
{
   $user = auth()->user();
    $latestBalance = FloatTransaction::where('user_id', $user->id)
        ->orderByDesc('id')
        ->value('balance_after') ?? 0;

    return [
        'id'            => $this->id,
        'amount'        => $this->amount,
        'reason'        => $this->reason,
        'requested_for' => $this->requested_for,
        'status'        => $this->status,
        'created_at'    => $this->created_at,
        'updated_at'    => $this->updated_at,

        'user' => [
            'id'      => $this->user->id,
            'name'    => $this->user->name,
            'email'   => $this->user->email,
            'balance' => $latestBalance, // 🔴 Float balance added here
        ],

        'approved_by' => $this->approved_by ? [
            'id'    => $this->approver->id,
            'name'  => $this->approver->name,
            'email' => $this->approver->email,
        ] : null,

        'approved_at' => $this->approved_at,
    ];
}

}
