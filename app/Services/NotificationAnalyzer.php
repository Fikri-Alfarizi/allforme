<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class NotificationAnalyzer
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Analyze user's financial health and generate notifications
     */
    public function analyzeUser(User $user): array
    {
        $notifications = [];
        
        // 1. Spending Pattern Analysis
        $spendingAlert = $this->analyzeSpendingPatterns($user);
        if ($spendingAlert) $notifications[] = $spendingAlert;
        
        // 2. Income Adequacy Analysis
        $incomeAlert = $this->analyzeIncomeAdequacy($user);
        if ($incomeAlert) $notifications[] = $incomeAlert;
        
        // 3. Future Balance Prediction
        $predictionAlert = $this->predictFutureBalance($user);
        if ($predictionAlert) $notifications[] = $predictionAlert;
        
        // 4. Target Suggestions
        $targetAlert = $this->suggestTargets($user);
        if ($targetAlert) $notifications[] = $targetAlert;
        
        // 5. Emergency Fund Check
        $emergencyAlert = $this->checkEmergencyFund($user);
        if ($emergencyAlert) $notifications[] = $emergencyAlert;
        
        // 6. Budget Warning
        $budgetAlert = $this->checkBudgetStatus($user);
        if ($budgetAlert) $notifications[] = $budgetAlert;
        
        // 7. Savings Milestone
        $milestoneAlert = $this->checkSavingsMilestone($user);
        if ($milestoneAlert) $notifications[] = $milestoneAlert;
        
        return $notifications;
    }

    /**
     * Analyze spending patterns - detect overspending
     */
    private function analyzeSpendingPatterns(User $user): ?array
    {
        $currentMonth = $this->financeService->getTotalExpenses(
            $user, 
            now()->startOfMonth(), 
            now()
        );
        
        $avgLast3Months = $this->financeService->getAverageMonthlyExpenses($user, 3);
        
        if ($avgLast3Months == 0) return null;
        
        $increasePercent = (($currentMonth - $avgLast3Months) / $avgLast3Months) * 100;
        
        // Alert if spending increased by more than 30%
        if ($increasePercent > 30) {
            return [
                'type' => 'spending_alert',
                'icon' => 'exclamation-triangle',
                'title' => 'Pengeluaran Meningkat Drastis',
                'message' => sprintf(
                    'Pengeluaran bulan ini %.0f%% lebih tinggi dari rata-rata. Anda sudah menghabiskan Rp %s vs rata-rata Rp %s',
                    $increasePercent,
                    number_format($currentMonth, 0, ',', '.'),
                    number_format($avgLast3Months, 0, ',', '.')
                ),
                'severity' => 'warning',
                'action_url' => '/finance',
                'data' => [
                    'current_spending' => $currentMonth,
                    'average_spending' => $avgLast3Months,
                    'increase_percent' => round($increasePercent, 1)
                ]
            ];
        }
        
        return null;
    }

    /**
     * Analyze income adequacy for financial goals
     */
    private function analyzeIncomeAdequacy(User $user): ?array
    {
        $emergencyFund = $user->emergencyFund;
        if (!$emergencyFund || $emergencyFund->isTargetReached()) return null;
        
        $remaining = $emergencyFund->remaining_amount;
        $monthlyIncome = $this->financeService->getTotalIncome(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        $savingRate = $this->financeService->getSavingRate(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        // If saving rate is below 10%, suggest additional income
        if ($savingRate < 10 && $remaining > 0) {
            $neededMonthly = $remaining / 6; // Target to complete in 6 months
            $currentSavings = ($savingRate / 100) * $monthlyIncome;
            $additionalNeeded = $neededMonthly - $currentSavings;
            
            if ($additionalNeeded > 0) {
                return [
                    'type' => 'income_recommendation',
                    'icon' => 'dollar-sign',
                    'title' => 'Butuh Penghasilan Tambahan',
                    'message' => sprintf(
                        'Untuk mencapai target dana darurat dalam 6 bulan, Anda perlu tambahan penghasilan Rp %s/bulan atau tingkatkan savings rate',
                        number_format($additionalNeeded, 0, ',', '.')
                    ),
                    'severity' => 'info',
                    'action_url' => '/emergency-fund',
                    'data' => [
                        'additional_income_needed' => $additionalNeeded,
                        'current_saving_rate' => $savingRate,
                        'target_months' => 6
                    ]
                ];
            }
        }
        
        return null;
    }

    /**
     * Predict future balance based on current burn rate
     */
    private function predictFutureBalance(User $user): ?array
    {
        $currentBalance = $this->financeService->getNetIncome($user);
        $burnRate = $this->financeService->getBurnRate($user);
        
        if ($burnRate <= 0 || $currentBalance <= 0) return null;
        
        $monthsUntilZero = $currentBalance / $burnRate;
        
        // Alert if balance will run out in less than 6 months
        if ($monthsUntilZero < 6) {
            $severity = $monthsUntilZero < 3 ? 'danger' : 'warning';
            
            return [
                'type' => 'future_prediction',
                'icon' => 'chart-line',
                'title' => 'Prediksi Saldo Kritis',
                'message' => sprintf(
                    'Dengan pola pengeluaran saat ini (Rp %s/bulan), saldo Anda akan habis dalam %.1f bulan. Pertimbangkan untuk mengurangi pengeluaran atau menambah income',
                    number_format($burnRate, 0, ',', '.'),
                    $monthsUntilZero
                ),
                'severity' => $severity,
                'action_url' => '/dashboard',
                'data' => [
                    'months_remaining' => round($monthsUntilZero, 1),
                    'burn_rate' => $burnRate,
                    'current_balance' => $currentBalance
                ]
            ];
        }
        
        return null;
    }

    /**
     * Suggest financial targets based on income
     */
    private function suggestTargets(User $user): ?array
    {
        $emergencyFund = $user->emergencyFund;
        
        // Only suggest if no emergency fund set
        if ($emergencyFund && $emergencyFund->target_amount > 0) return null;
        
        $avgExpenses = $this->financeService->getAverageMonthlyExpenses($user, 3);
        
        if ($avgExpenses == 0) return null;
        
        $recommendedTarget = $avgExpenses * 6; // 6 months of expenses
        
        return [
            'type' => 'target_suggestion',
            'icon' => 'bullseye',
            'title' => 'Rekomendasi Target Dana Darurat',
            'message' => sprintf(
                'Berdasarkan pengeluaran bulanan Anda (Rp %s), target dana darurat ideal adalah Rp %s (6 bulan pengeluaran). Mulai sisihkan 20%% dari penghasilan',
                number_format($avgExpenses, 0, ',', '.'),
                number_format($recommendedTarget, 0, ',', '.')
            ),
            'severity' => 'info',
            'action_url' => '/emergency-fund',
            'data' => [
                'recommended_target' => $recommendedTarget,
                'monthly_expenses' => $avgExpenses,
                'months_covered' => 6
            ]
        ];
    }

    /**
     * Check emergency fund status
     */
    private function checkEmergencyFund(User $user): ?array
    {
        $emergencyFund = $user->emergencyFund;
        
        if (!$emergencyFund || $emergencyFund->target_amount == 0) return null;
        
        $progress = $emergencyFund->progress_percentage;
        
        // Alert if emergency fund is below 50%
        if ($progress < 50) {
            $remaining = $emergencyFund->remaining_amount;
            $monthlyContribution = $emergencyFund->getMonthlySavingsNeeded(12);
            
            return [
                'type' => 'emergency_fund',
                'icon' => 'shield-alt',
                'title' => 'Dana Darurat Perlu Perhatian',
                'message' => sprintf(
                    'Dana darurat Anda hanya %.0f%% dari target (Rp %s dari Rp %s). Prioritaskan menabung minimal Rp %s/bulan',
                    $progress,
                    number_format($emergencyFund->current_amount, 0, ',', '.'),
                    number_format($emergencyFund->target_amount, 0, ',', '.'),
                    number_format($monthlyContribution, 0, ',', '.')
                ),
                'severity' => 'warning',
                'action_url' => '/emergency-fund',
                'data' => [
                    'progress_percent' => $progress,
                    'current_amount' => $emergencyFund->current_amount,
                    'target_amount' => $emergencyFund->target_amount,
                    'monthly_contribution_needed' => $monthlyContribution
                ]
            ];
        }
        
        return null;
    }

    /**
     * Check budget status - warn if expenses exceed 80% of income
     */
    private function checkBudgetStatus(User $user): ?array
    {
        $income = $this->financeService->getTotalIncome(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        $expenses = $this->financeService->getTotalExpenses(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        if ($income == 0) return null;
        
        $expenseRatio = ($expenses / $income) * 100;
        
        // Alert if expenses exceed 80% of income
        if ($expenseRatio > 80) {
            $remaining = $income - $expenses;
            $severity = $expenseRatio > 95 ? 'danger' : 'warning';
            
            return [
                'type' => 'budget_warning',
                'icon' => 'exclamation-circle',
                'title' => 'Budget Hampir Habis',
                'message' => sprintf(
                    'Pengeluaran sudah mencapai %.0f%% dari income! Sisa budget: Rp %s. Hati-hati dengan pengeluaran hingga akhir bulan',
                    $expenseRatio,
                    number_format($remaining, 0, ',', '.')
                ),
                'severity' => $severity,
                'action_url' => '/finance',
                'data' => [
                    'expense_ratio' => round($expenseRatio, 1),
                    'remaining_budget' => $remaining,
                    'total_income' => $income,
                    'total_expenses' => $expenses
                ]
            ];
        }
        
        return null;
    }

    /**
     * Check for savings milestones
     */
    private function checkSavingsMilestone(User $user): ?array
    {
        $netIncome = $this->financeService->getNetIncome(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        $savingRate = $this->financeService->getSavingRate(
            $user,
            now()->startOfMonth(),
            now()
        );
        
        // Celebrate if saved more than 1 million this month
        if ($netIncome >= 1000000) {
            return [
                'type' => 'savings_milestone',
                'icon' => 'trophy',
                'title' => 'Pencapaian Luar Biasa!',
                'message' => sprintf(
                    'Selamat! Anda berhasil menabung Rp %s bulan ini. Savings rate: %.1f%%. Pertahankan kebiasaan baik ini!',
                    number_format($netIncome, 0, ',', '.'),
                    $savingRate
                ),
                'severity' => 'success',
                'action_url' => '/dashboard',
                'data' => [
                    'net_income' => $netIncome,
                    'saving_rate' => $savingRate
                ]
            ];
        }
        
        // Celebrate if saving rate is above 30%
        if ($savingRate > 30 && $netIncome > 0) {
            return [
                'type' => 'savings_milestone',
                'icon' => 'check-circle',
                'title' => 'Savings Rate Excellent!',
                'message' => sprintf(
                    'Luar biasa! Savings rate Anda mencapai %.1f%%. Anda berhasil menabung Rp %s bulan ini',
                    $savingRate,
                    number_format($netIncome, 0, ',', '.')
                ),
                'severity' => 'success',
                'action_url' => '/dashboard',
                'data' => [
                    'saving_rate' => $savingRate,
                    'net_income' => $netIncome
                ]
            ];
        }
        
        return null;
    }
}
