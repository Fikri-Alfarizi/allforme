<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RecurringExpense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'amount',
        'period',
        'next_due_date',
        'is_active',
        'reminder_days_before',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
        'reminder_days_before' => 'integer',
    ];

    /**
     * Get the user that owns the recurring expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this recurring expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Scope to get only active recurring expenses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get upcoming due expenses.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('is_active', true)
                     ->where('next_due_date', '<=', now()->addDays($days))
                     ->where('next_due_date', '>=', now());
    }

    /**
     * Scope to get overdue expenses.
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_active', true)
                     ->where('next_due_date', '<', now());
    }

    /**
     * Check if reminder should be sent.
     */
    public function shouldSendReminder(): bool
    {
        if (!$this->is_active || !$this->reminder_days_before) {
            return false;
        }
        
        $reminderDate = $this->next_due_date->subDays($this->reminder_days_before);
        return now()->isSameDay($reminderDate);
    }

    /**
     * Calculate next due date based on period.
     */
    public function calculateNextDueDate(): Carbon
    {
        $currentDueDate = $this->next_due_date;
        
        return match($this->period) {
            'daily' => $currentDueDate->addDay(),
            'weekly' => $currentDueDate->addWeek(),
            'monthly' => $currentDueDate->addMonth(),
            'yearly' => $currentDueDate->addYear(),
            default => $currentDueDate,
        };
    }

    /**
     * Mark as paid and update next due date.
     */
    public function markAsPaid(): bool
    {
        $this->next_due_date = $this->calculateNextDueDate();
        return $this->save();
    }

    /**
     * Get days until due.
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->next_due_date, false);
    }

    /**
     * Check if overdue.
     */
    public function isOverdue(): bool
    {
        return $this->next_due_date->isPast();
    }
}
