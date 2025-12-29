<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'reminder_at',
        'completed_at',
        'tags',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get in-progress tasks.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get high priority tasks.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Scope to get overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope to get upcoming tasks.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', '!=', 'completed')
                     ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope to get tasks due today.
     */
    public function scopeDueToday($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->whereDate('due_date', today());
    }

    /**
     * Mark task as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Mark task as in progress.
     */
    public function markAsInProgress(): bool
    {
        $this->status = 'in_progress';
        return $this->save();
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' 
               && $this->due_date 
               && $this->due_date->isPast();
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get days until due.
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if reminder should be sent.
     */
    public function shouldSendReminder(): bool
    {
        if (!$this->reminder_at || $this->isCompleted()) {
            return false;
        }
        
        return now()->gte($this->reminder_at);
    }
}
