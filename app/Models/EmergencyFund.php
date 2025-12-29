<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyFund extends Model
{
    protected $fillable = [
        'user_id',
        'target_amount',
        'current_amount',
        'monthly_expense_base',
        'target_months',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'monthly_expense_base' => 'decimal:2',
        'target_months' => 'integer',
    ];

    /**
     * Get the user that owns the emergency fund.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount == 0) {
            return 0;
        }
        
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    /**
     * Calculate remaining amount needed.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Calculate months of expenses covered.
     */
    public function getMonthsCoveredAttribute(): float
    {
        if ($this->monthly_expense_base == 0) {
            return 0;
        }
        
        return $this->current_amount / $this->monthly_expense_base;
    }

    /**
     * Check if target is reached.
     */
    public function isTargetReached(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Calculate monthly savings needed to reach target in given months.
     */
    public function getMonthlySavingsNeeded(int $months = null): float
    {
        $targetMonths = $months ?? 12; // Default 12 months
        
        if ($targetMonths == 0) {
            return 0;
        }
        
        return $this->remaining_amount / $targetMonths;
    }

    /**
     * Add funds to emergency fund.
     */
    public function addFunds(float $amount): bool
    {
        $this->current_amount += $amount;
        return $this->save();
    }

    /**
     * Withdraw funds from emergency fund.
     */
    public function withdrawFunds(float $amount): bool
    {
        if ($amount > $this->current_amount) {
            return false;
        }
        
        $this->current_amount -= $amount;
        return $this->save();
    }
}
