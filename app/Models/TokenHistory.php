<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenHistory extends Model
{
     protected $fillable = [
        'user_id',
        'amount',
        'balance_after',
        'type',
        'created_by',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
