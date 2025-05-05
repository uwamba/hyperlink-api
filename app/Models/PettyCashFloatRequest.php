<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/PettyCashFloatRequest.php

class PettyCashFloatRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'reason', 'status', 'approved_by', 'approved_at', 'requested_for'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
