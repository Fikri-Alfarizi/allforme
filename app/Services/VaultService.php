<?php

namespace App\Services;

use App\Models\User;
use App\Models\AccountVault;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Collection;

class VaultService
{
    /**
     * Create a new account in vault.
     */
    public function createAccount(
        User $user,
        string $accountType,
        string $serviceName,
        string $username,
        string $password,
        ?string $email = null,
        ?string $notes = null,
        ?string $url = null
    ): AccountVault {
        return AccountVault::create([
            'user_id' => $user->id,
            'account_type' => $accountType,
            'service_name' => $serviceName,
            'username' => $username, // Will be auto-encrypted
            'email' => $email, // Will be auto-encrypted
            'password' => $password, // Will be auto-encrypted
            'notes' => $notes, // Will be auto-encrypted
            'url' => $url,
            'last_password_change' => now(),
        ]);
    }

    /**
     * Update account password.
     */
    public function updatePassword(AccountVault $account, string $newPassword): bool
    {
        return $account->updatePassword($newPassword);
    }

    /**
     * Get all accounts for a user.
     */
    public function getAllAccounts(User $user): Collection
    {
        return $user->accountsVault()->orderBy('service_name')->get();
    }

    /**
     * Get accounts by type.
     */
    public function getAccountsByType(User $user, string $type): Collection
    {
        return $user->accountsVault()->ofType($type)->get();
    }

    /**
     * Search accounts.
     */
    public function searchAccounts(User $user, string $query): Collection
    {
        return $user->accountsVault()
            ->where('service_name', 'like', "%{$query}%")
            ->orWhere('url', 'like', "%{$query}%")
            ->get();
    }

    /**
     * Get accounts with old passwords.
     */
    public function getAccountsWithOldPasswords(User $user, int $days = 90): Collection
    {
        return $user->accountsVault()->get()->filter(function ($account) use ($days) {
            return $account->isPasswordOld($days);
        });
    }

    /**
     * Delete account from vault.
     */
    public function deleteAccount(AccountVault $account): bool
    {
        return $account->delete();
    }

    /**
     * Export vault data (encrypted).
     */
    public function exportVault(User $user): array
    {
        $accounts = $this->getAllAccounts($user);
        
        return [
            'exported_at' => now()->toIso8601String(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'accounts_count' => $accounts->count(),
            'accounts' => $accounts->map(function ($account) {
                return [
                    'service_name' => $account->service_name,
                    'account_type' => $account->account_type,
                    'username' => $account->username, // Already decrypted by accessor
                    'email' => $account->email,
                    'password' => $account->password,
                    'notes' => $account->notes,
                    'url' => $account->url,
                    'last_password_change' => $account->last_password_change?->toDateString(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Get vault statistics.
     */
    public function getVaultStatistics(User $user): array
    {
        $accounts = $this->getAllAccounts($user);
        
        $byType = $accounts->groupBy('account_type')->map->count();
        $oldPasswords = $this->getAccountsWithOldPasswords($user)->count();
        
        return [
            'total_accounts' => $accounts->count(),
            'by_type' => $byType->toArray(),
            'old_passwords_count' => $oldPasswords,
            'last_added' => $accounts->sortByDesc('created_at')->first()?->created_at?->diffForHumans(),
        ];
    }

    /**
     * Generate strong password.
     */
    public function generateStrongPassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Check password strength.
     */
    public function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];
        
        // Length check
        $length = strlen($password);
        if ($length >= 12) {
            $score += 25;
        } elseif ($length >= 8) {
            $score += 15;
            $feedback[] = 'Password bisa lebih panjang (minimal 12 karakter)';
        } else {
            $feedback[] = 'Password terlalu pendek (minimal 8 karakter)';
        }
        
        // Uppercase check
        if (preg_match('/[A-Z]/', $password)) {
            $score += 25;
        } else {
            $feedback[] = 'Tambahkan huruf besar';
        }
        
        // Lowercase check
        if (preg_match('/[a-z]/', $password)) {
            $score += 25;
        } else {
            $feedback[] = 'Tambahkan huruf kecil';
        }
        
        // Number check
        if (preg_match('/[0-9]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Tambahkan angka';
        }
        
        // Symbol check
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 10;
        } else {
            $feedback[] = 'Tambahkan simbol (!@#$%^&*)';
        }
        
        $strength = match(true) {
            $score >= 90 => 'very_strong',
            $score >= 70 => 'strong',
            $score >= 50 => 'medium',
            $score >= 30 => 'weak',
            default => 'very_weak',
        };
        
        return [
            'score' => $score,
            'strength' => $strength,
            'feedback' => $feedback,
        ];
    }
}
