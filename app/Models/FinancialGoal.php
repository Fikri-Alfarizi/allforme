<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialGoal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'target_amount',
        'current_amount',
        'target_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];
}
