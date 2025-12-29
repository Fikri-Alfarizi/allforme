<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'language',
        'timezone',
        'theme',
        'notification_enabled',
        'ai_enabled',
        'vault_timeout_minutes',
        'preferences',
    ];

    protected $casts = [
        'notification_enabled' => 'boolean',
        'ai_enabled' => 'boolean',
        'vault_timeout_minutes' => 'integer',
        'preferences' => 'array',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a preference value.
     */
    public function getPreference(string $key, $default = null)
    {
        $preferences = $this->preferences ?? [];
        return $preferences[$key] ?? $default;
    }

    /**
     * Set a preference value.
     */
    public function setPreference(string $key, $value): bool
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        return $this->save();
    }

    /**
     * Remove a preference.
     */
    public function removePreference(string $key): bool
    {
        $preferences = $this->preferences ?? [];
        
        if (isset($preferences[$key])) {
            unset($preferences[$key]);
            $this->preferences = $preferences;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Check if notifications are enabled.
     */
    public function hasNotificationsEnabled(): bool
    {
        return $this->notification_enabled;
    }

    /**
     * Check if AI is enabled.
     */
    public function hasAIEnabled(): bool
    {
        return $this->ai_enabled;
    }

    /**
     * Get vault timeout in seconds.
     */
    public function getVaultTimeoutSecondsAttribute(): int
    {
        return $this->vault_timeout_minutes * 60;
    }
}
