<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ChatSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'email', 'phone', 'location',
        'client_id', 'status', 'issue_category',
        'is_verified_client', 'agent_joined_at',
    ];

    protected $casts = [
        'is_verified_client' => 'boolean',
        'agent_joined_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'session_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}