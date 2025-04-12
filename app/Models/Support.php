<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Support extends Model

{
 
    use  HasUuids;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'email',
        'description',
        'address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
    ];

    /**
     * Relationship: A support ticket belongs to a client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
