<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'icon',
        'color',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all financial transactions for this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'category_id');
    }

    /**
     * Get all recurring expenses for this category.
     */
    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class, 'category_id');
    }

    /**
     * Scope to get only system categories.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get only user custom categories.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to get categories for a specific user (including system categories).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });
    }
}
