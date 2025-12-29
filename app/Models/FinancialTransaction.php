<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'category_id',
        'amount',
        'source',
        'description',
        'transaction_date',
        'is_recurring',
        'recurring_period',
        'tags',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Scope to get only income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to get only expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope to get transactions for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get transactions for current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('transaction_date', now()->year)
                     ->whereMonth('transaction_date', now()->month);
    }

    /**
     * Scope to get recurring transactions.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Check if this is an income transaction.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Check if this is an expense transaction.
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }
}
