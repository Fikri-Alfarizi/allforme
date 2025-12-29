<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all financial transactions for the user.
     */
    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Get all expense categories for the user (custom categories).
     */
    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get the user's emergency fund.
     */
    public function emergencyFund()
    {
        return $this->hasOne(EmergencyFund::class);
    }

    /**
     * Get all recurring expenses for the user.
     */
    public function recurringExpenses()
    {
        return $this->hasMany(RecurringExpense::class);
    }

    /**
     * Get all accounts in the vault.
     */
    public function accountsVault()
    {
        return $this->hasMany(AccountVault::class);
    }

    /**
     * Get all notes for the user.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all tasks for the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all AI conversation logs.
     */
    public function aiLogs()
    {
        return $this->hasMany(AiLog::class);
    }

    /**
     * Get the user's settings.
     */
    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Get income transactions.
     */
    public function incomeTransactions()
    {
        return $this->financialTransactions()->where('type', 'income');
    }

    /**
    /**
     * Get expense transactions.
     */
    public function expenseTransactions()
    {
        return $this->financialTransactions()->where('type', 'expense');
    }

    /**
     * Get all digital accounts.
     */
    public function digitalAccounts()
    {
        return $this->hasMany(DigitalAccount::class);
    }
}
