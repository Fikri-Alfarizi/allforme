<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'title',
        'is_pinned',
        'prompt',
        'response',
        'context_data',
        'tokens_used',
        'response_time',
    ];

    protected $casts = [
        'context_data' => 'array',
        'tokens_used' => 'integer',
        'response_time' => 'integer',
    ];

    /**
     * Get the user that owns the AI log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get recent conversations.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get logs from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get formatted response time.
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if (!$this->response_time) {
            return 'N/A';
        }
        
        if ($this->response_time < 1000) {
            return $this->response_time . 'ms';
        }
        
        return round($this->response_time / 1000, 2) . 's';
    }

    /**
     * Get conversation summary.
     */
    public function getSummaryAttribute(): string
    {
        return \Str::limit($this->prompt, 50);
    }
}
