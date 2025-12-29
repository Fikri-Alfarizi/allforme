<?php

namespace App\Services;

use App\Models\User;
use App\Models\FinancialTransaction;
use App\Models\EmergencyFund;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceService
{
    /**
     * Calculate total income for a user in a given period.
     */
    public function getTotalIncome(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = $user->financialTransactions()->income();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return (float) $query->sum('amount');
    }

    /**
     * Calculate total expenses for a user in a given period.
     */
    public function getTotalExpenses(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = $user->financialTransactions()->expense();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return (float) $query->sum('amount');
    }

    /**
     * Calculate net income (income - expenses).
     */
    public function getNetIncome(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $income = $this->getTotalIncome($user, $startDate, $endDate);
        $expenses = $this->getTotalExpenses($user, $startDate, $endDate);
        
        return $income - $expenses;
    }

    /**
     * Get current month's financial summary.
     */
    public function getCurrentMonthSummary(User $user): array
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $income = $this->getTotalIncome($user, $startDate, $endDate);
        $expenses = $this->getTotalExpenses($user, $startDate, $endDate);
        $netIncome = $income - $expenses;
        
        return [
            'income' => $income,
            'expenses' => $expenses,
            'net_income' => $netIncome,
            'saving_rate' => $income > 0 ? ($netIncome / $income) * 100 : 0,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Get expenses breakdown by category.
     */
    public function getExpensesByCategory(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = $user->financialTransactions()
            ->expense()
            ->with('category');
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return $query->get()
            ->groupBy('category.name')
            ->map(function ($transactions, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Uncategorized',
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                    'percentage' => 0, // Will be calculated later
                ];
            });
    }

    /**
     * Calculate average monthly expenses.
     */
    public function getAverageMonthlyExpenses(User $user, int $months = 3): float
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $totalExpenses = $this->getTotalExpenses($user, $startDate, $endDate);
        
        return $totalExpenses / $months;
    }

    /**
     * Calculate burn rate (monthly spending rate).
     */
    public function getBurnRate(User $user): float
    {
        return $this->getAverageMonthlyExpenses($user, 3);
    }

    /**
     * Calculate saving rate percentage.
     */
    public function getSavingRate(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $income = $this->getTotalIncome($user, $startDate, $endDate);
        
        if ($income == 0) {
            return 0;
        }
        
        $netIncome = $this->getNetIncome($user, $startDate, $endDate);
        
        return ($netIncome / $income) * 100;
    }

    /**
     * Get cashflow data for charts (last N months).
     */
    /**
     * Get cashflow data for charts (weekly, monthly, yearly).
     * @param User $user
     * @param string $period 'weekly' | 'monthly' | 'yearly'
     * @return array
     */
    public function getCashflowData(User $user, string $period = 'monthly'): array
    {
        $data = [];
        
        switch ($period) {
            case 'weekly':
                // Last 12 weeks
                for ($i = 11; $i >= 0; $i--) {
                    $startDate = now()->subWeeks($i)->startOfWeek();
                    $endDate = now()->subWeeks($i)->endOfWeek();
                    $label = 'W' . $startDate->weekOfYear . ' ' . $startDate->format('M');
                    
                    $this->addCashflowPeriod($data, $user, $startDate, $endDate, $label);
                }
                break;
                
            case 'yearly':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $startDate = now()->subYears($i)->startOfYear();
                    $endDate = now()->subYears($i)->endOfYear();
                    $label = $startDate->format('Y');
                    
                    $this->addCashflowPeriod($data, $user, $startDate, $endDate, $label);
                }
                break;
                
            case 'monthly':
            default:
                // Last 6 months (Default)
                for ($i = 5; $i >= 0; $i--) {
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();
                    $label = $startDate->format('M Y');
                    
                    $this->addCashflowPeriod($data, $user, $startDate, $endDate, $label);
                }
                break;
        }
        
        return $data;
    }

    /**
     * Helper to add period data to array.
     */
    private function addCashflowPeriod(array &$data, User $user, Carbon $startDate, Carbon $endDate, string $label): void
    {
        $income = $this->getTotalIncome($user, $startDate, $endDate);
        $expenses = $this->getTotalExpenses($user, $startDate, $endDate);
        
        $data[] = [
            'period' => $label, // Changed from 'month' to 'period' to be generic
            'income' => $income,
            'expenses' => $expenses,
            'net' => $income - $expenses,
        ];
    }

    /**
     * Predict when balance will run out based on current burn rate.
     */
    public function getCashRunway(User $user, float $currentBalance): ?int
    {
        $burnRate = $this->getBurnRate($user);
        
        if ($burnRate <= 0) {
            return null; // No expenses or positive cashflow
        }
        
        return (int) ceil($currentBalance / $burnRate);
    }

    /**
     * Get financial health score (0-100).
     */
    public function getFinancialHealthScore(User $user): array
    {
        $score = 0;
        $factors = [];
        
        // Factor 1: Saving rate (30 points)
        $savingRate = $this->getSavingRate($user, now()->startOfMonth(), now()->endOfMonth());
        $savingScore = min(30, ($savingRate / 30) * 30); // 30% saving rate = full points
        $score += $savingScore;
        $factors['saving_rate'] = [
            'score' => $savingScore,
            'value' => $savingRate,
            'max' => 30,
        ];
        
        // Factor 2: Emergency fund (40 points)
        $emergencyFund = $user->emergencyFund;
        $emergencyScore = 0;
        if ($emergencyFund) {
            $emergencyScore = min(40, ($emergencyFund->progress_percentage / 100) * 40);
        }
        $score += $emergencyScore;
        $factors['emergency_fund'] = [
            'score' => $emergencyScore,
            'value' => $emergencyFund ? $emergencyFund->progress_percentage : 0,
            'max' => 40,
        ];
        
        // Factor 3: Positive cashflow (30 points)
        $netIncome = $this->getNetIncome($user, now()->startOfMonth(), now()->endOfMonth());
        $cashflowScore = $netIncome > 0 ? 30 : 0;
        $score += $cashflowScore;
        $factors['positive_cashflow'] = [
            'score' => $cashflowScore,
            'value' => $netIncome,
            'max' => 30,
        ];
        
        return [
            'total_score' => round($score, 2),
            'grade' => $this->getGradeFromScore($score),
            'factors' => $factors,
        ];
    }

    /**
     * Convert score to grade.
     */
    private function getGradeFromScore(float $score): string
    {
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    /**
     * Get AI context data for financial analysis.
     */
    public function getAIContextData(User $user): array
    {
        $summary = $this->getCurrentMonthSummary($user);
        $emergencyFund = $user->emergencyFund;
        
        // Get recent transactions for detailed analysis
        $recentTransactions = $user->financialTransactions()
            ->orderBy('transaction_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function($t) {
                return [
                    'date' => $t->transaction_date->format('d M'),
                    'type' => $t->type,
                    'amount' => $t->amount,
                    'description' => $t->description,
                    'category' => $t->category->name ?? 'Uncategorized',
                ];
            });
        
        // Get expense breakdown by category
        $expenseByCategory = $user->financialTransactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function($transactions, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Uncategorized',
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                ];
            })
            ->values();
        
        // Get income sources
        $incomeSources = $user->financialTransactions()
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->get()
            ->groupBy('source')
            ->map(function($transactions, $source) {
                return [
                    'source' => $source ?? 'Unknown',
                    'total' => $transactions->sum('amount'),
                ];
            })
            ->values();
        
        // Get digital accounts/wallets data
        $digitalAccounts = \App\Models\DigitalAccount::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereNull('user_id');
        })->get();
        
        $totalDigitalAssets = $digitalAccounts->sum('current_balance');
        
        $accountsList = $digitalAccounts->map(function($account) {
            return [
                'platform' => $account->platform_name,
                'balance' => $account->current_balance,
                'type' => $account->type,
                'currency' => $account->currency ?? 'IDR',
            ];
        })->values();
        
        $byType = $digitalAccounts->groupBy('type')->map(function($accounts) {
            return $accounts->sum('current_balance');
        });
        
        return [
            'current_month' => $summary,
            'recent_transactions' => $recentTransactions,
            'expense_by_category' => $expenseByCategory,
            'income_sources' => $incomeSources,
            'digital_assets' => [
                'total' => $totalDigitalAssets,
                'total_formatted' => 'Rp ' . number_format($totalDigitalAssets, 0, ',', '.'),
                'accounts' => $accountsList,
                'by_type' => $byType,
            ],
            'emergency_fund' => $emergencyFund ? [
                'target' => $emergencyFund->target_amount,
                'current' => $emergencyFund->current_amount,
                'progress' => $emergencyFund->progress_percentage,
                'months_covered' => $emergencyFund->months_covered,
            ] : null,
            'average_monthly_expenses' => $this->getAverageMonthlyExpenses($user, 3),
            'burn_rate' => $this->getBurnRate($user),
            'saving_rate' => $this->getSavingRate($user, now()->startOfMonth(), now()->endOfMonth()),
            'health_score' => $this->getFinancialHealthScore($user),
        ];
    }
}
