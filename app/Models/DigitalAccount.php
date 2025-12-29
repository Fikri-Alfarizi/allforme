<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalAccount extends Model
{
    protected $fillable = [
        'user_id',
        'platform_name',
        'type',
        'current_balance',
        'currency',
        'website_url',
        'is_system',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
