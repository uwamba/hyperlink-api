<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'user_id',
        'description',
    ];

    // Optional: relation to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Optional: get a simple model name
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }

    // Optional: make description more readable
    public function getFormattedDescriptionAttribute(): string
    {
        return "[{$this->action}] {$this->model_name} ID: {$this->model_id} by User #{$this->user_id}";
    }
}
