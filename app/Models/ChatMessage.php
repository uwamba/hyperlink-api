<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ChatMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id', 'sender', 'message', 'agent_id',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}