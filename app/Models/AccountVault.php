<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountVault extends Model
{
    protected $table = 'accounts_vault';

    protected $fillable = [
        'user_id',
        'account_type',
        'service_name',
        'username',
        'email',
        'password',
        'notes',
        'url',
        'last_password_change',
    ];

    protected $casts = [
        'last_password_change' => 'date',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by account type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Check if password is old (more than 90 days).
     */
    public function isPasswordOld(int $days = 90): bool
    {
        if (!$this->last_password_change) {
            return true;
        }
        
        return $this->last_password_change->diffInDays(now()) > $days;
    }

    /**
     * Update password and timestamp.
     */
    public function updatePassword(string $newPassword): bool
    {
        $this->password = $newPassword;
        $this->last_password_change = now();
        return $this->save();
    }
}
