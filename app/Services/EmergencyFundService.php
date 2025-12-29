<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmergencyFund;

class EmergencyFundService
{
    /**
     * Initialize emergency fund for a user.
     */
    public function initialize(User $user, float $monthlyExpenseBase, int $targetMonths = 6): EmergencyFund
    {
        $targetAmount = $monthlyExpenseBase * $targetMonths;
        
        return EmergencyFund::updateOrCreate(
            ['user_id' => $user->id],
            [
                'target_amount' => $targetAmount,
                'monthly_expense_base' => $monthlyExpenseBase,
                'target_months' => $targetMonths,
                'current_amount' => $user->emergencyFund?->current_amount ?? 0,
            ]
        );
    }

    /**
     * Update target based on new monthly expenses.
     */
    public function updateTarget(EmergencyFund $fund, float $newMonthlyExpense): bool
    {
        $fund->monthly_expense_base = $newMonthlyExpense;
        $fund->target_amount = $newMonthlyExpense * $fund->target_months;
        
        return $fund->save();
    }

    /**
     * Set target amount directly.
     */
    public function setTargetAmount(EmergencyFund $fund, float $amount): bool
    {
        $fund->target_amount = $amount;
        // Optional: Update monthly base inversely or keep as is? 
        // For simplicity, let's keep monthly base as is, as target might be arbitrary.
        return $fund->save();
    }

    /**
     * Add contribution to emergency fund.
     */
    public function addContribution(EmergencyFund $fund, float $amount, string $note = null): bool
    {
        $success = $fund->addFunds($amount);
        
        if ($success) {
            // Create expense transaction (money goes to emergency fund)
            \App\Models\FinancialTransaction::create([
                'user_id' => $fund->user_id,
                'type' => 'expense',
                'amount' => $amount,
                'description' => $note ?? 'Menabung ke Dana Darurat',
                'transaction_date' => now(),
                'source' => 'Emergency Fund',
            ]);
        }
        
        return $success;
    }

    /**
     * Withdraw from emergency fund.
     */
    public function withdraw(EmergencyFund $fund, float $amount, string $reason = null): bool
    {
        $success = $fund->withdrawFunds($amount);
        
        if ($success) {
            // Create income transaction (money comes back from emergency fund)
            \App\Models\FinancialTransaction::create([
                'user_id' => $fund->user_id,
                'type' => 'income',
                'amount' => $amount,
                'description' => $reason ?? 'Penarikan dari Dana Darurat',
                'transaction_date' => now(),
                'source' => 'Emergency Fund',
            ]);
        }
        
        return $success;
    }

    /**
     * Calculate recommended monthly contribution.
     */
    public function getRecommendedMonthlyContribution(EmergencyFund $fund, int $targetMonths = 12): float
    {
        return $fund->getMonthlySavingsNeeded($targetMonths);
    }

    /**
     * Get progress report.
     */
    public function getProgressReport(EmergencyFund $fund): array
    {
        $recommended = $this->getRecommendedMonthlyContribution($fund);
        $remaining = $fund->remaining_amount;
        $monthsToTarget = $recommended > 0 ? ceil($remaining / $recommended) : 0;
        
        $percentage = $fund->progress_percentage;
        $status = 'needs_attention';
        if ($percentage >= 100) $status = 'completed';
        elseif ($percentage >= 80) $status = 'on_track';
        elseif ($percentage >= 50) $status = 'good';
        elseif ($percentage >= 25) $status = 'fair';

        return [
            'current_amount' => $fund->current_amount,
            'target_amount' => $fund->target_amount,
            'remaining_amount' => $fund->remaining_amount,
            'progress_percentage' => $fund->progress_percentage,
            'months_covered' => $fund->months_covered,
            'target_months' => $fund->target_months,
            'is_target_reached' => $fund->isTargetReached(),
            'recommended_monthly' => $recommended,
            'months_to_target' => $monthsToTarget,
            'status' => $status,
        ];
    }

    /**
     * Get AI recommendations for emergency fund.
     */
    public function getAIRecommendations(EmergencyFund $fund): array
    {
        $recommendations = [];
        
        if ($fund->progress_percentage < 25) {
            $recommendations[] = [
                'priority' => 'high',
                'message' => 'Dana darurat Anda masih sangat rendah. Prioritaskan untuk menabung minimal 20% dari penghasilan.',
            ];
        } elseif ($fund->progress_percentage < 50) {
            $recommendations[] = [
                'priority' => 'medium',
                'message' => 'Anda sudah memiliki dana darurat, tapi masih perlu ditingkatkan. Target minimal 50%.',
            ];
        } elseif ($fund->progress_percentage < 100) {
            $recommendations[] = [
                'priority' => 'low',
                'message' => 'Dana darurat Anda sudah cukup baik. Terus konsisten menabung hingga mencapai target.',
            ];
        } else {
            $recommendations[] = [
                'priority' => 'info',
                'message' => 'Selamat! Dana darurat Anda sudah mencapai target. Pertimbangkan untuk investasi.',
            ];
        }
        
        $monthlyContribution = $this->getRecommendedMonthlyContribution($fund);
        $recommendations[] = [
            'priority' => 'info',
            'message' => sprintf(
                'Untuk mencapai target dalam 12 bulan, sisihkan Rp %s per bulan.',
                number_format($monthlyContribution, 0, ',', '.')
            ),
        ];
        
        return $recommendations;
    }

    /**
     * Auto-calculate and update monthly expense base from user transactions.
     */
    public function autoUpdateMonthlyExpenseBase(User $user, FinanceService $financeService): bool
    {
        $fund = $user->emergencyFund;
        
        if (!$fund) {
            return false;
        }
        
        $averageExpenses = $financeService->getAverageMonthlyExpenses($user, 3);
        
        return $this->updateTarget($fund, $averageExpenses);
    }
}
