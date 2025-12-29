<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\RecurringExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Get all pending notifications for a user.
     */
    public function getPendingNotifications(User $user): array
    {
        $notifications = [];
        
        // Task reminders
        $taskReminders = $this->getTaskReminders($user);
        if ($taskReminders->isNotEmpty()) {
            $notifications['tasks'] = $taskReminders;
        }
        
        // Recurring expense reminders
        $expenseReminders = $this->getRecurringExpenseReminders($user);
        if ($expenseReminders->isNotEmpty()) {
            $notifications['recurring_expenses'] = $expenseReminders;
        }
        
        // Overdue tasks
        $overdueTasks = $this->getOverdueTasks($user);
        if ($overdueTasks->isNotEmpty()) {
            $notifications['overdue_tasks'] = $overdueTasks;
        }
        
        // Old passwords warning
        $oldPasswords = $this->getOldPasswordsWarning($user);
        if ($oldPasswords->isNotEmpty()) {
            $notifications['old_passwords'] = $oldPasswords;
        }
        
        // Emergency fund alerts
        $emergencyFundAlert = $this->getEmergencyFundAlert($user);
        if ($emergencyFundAlert) {
            $notifications['emergency_fund'] = $emergencyFundAlert;
        }
        
        return $notifications;
    }

    /**
     * Get task reminders.
     */
    private function getTaskReminders(User $user): Collection
    {
        return $user->tasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now())
            ->get()
            ->map(function ($task) {
                return [
                    'type' => 'task_reminder',
                    'priority' => $task->priority,
                    'title' => $task->title,
                    'due_date' => $task->due_date?->format('d M Y H:i'),
                    'message' => "Reminder: {$task->title}",
                ];
            });
    }

    /**
     * Get recurring expense reminders.
     */
    private function getRecurringExpenseReminders(User $user): Collection
    {
        return $user->recurringExpenses()
            ->active()
            ->upcoming(7)
            ->get()
            ->filter(function ($expense) {
                return $expense->shouldSendReminder();
            })
            ->map(function ($expense) {
                return [
                    'type' => 'recurring_expense_reminder',
                    'priority' => 'medium',
                    'title' => $expense->name,
                    'amount' => $expense->amount,
                    'due_date' => $expense->next_due_date->format('d M Y'),
                    'days_until_due' => $expense->days_until_due,
                    'message' => "Pembayaran {$expense->name} jatuh tempo dalam {$expense->days_until_due} hari",
                ];
            });
    }

    /**
     * Get overdue tasks.
     */
    private function getOverdueTasks(User $user): Collection
    {
        return $user->tasks()
            ->overdue()
            ->get()
            ->map(function ($task) {
                return [
                    'type' => 'overdue_task',
                    'priority' => 'high',
                    'title' => $task->title,
                    'due_date' => $task->due_date?->format('d M Y'),
                    'days_overdue' => abs($task->days_until_due),
                    'message' => "Task '{$task->title}' sudah terlambat " . abs($task->days_until_due) . " hari",
                ];
            });
    }

    /**
     * Get old passwords warning.
     */
    private function getOldPasswordsWarning(User $user): Collection
    {
        return $user->accountsVault()
            ->get()
            ->filter(function ($account) {
                return $account->isPasswordOld(90);
            })
            ->map(function ($account) {
                $daysSinceChange = $account->last_password_change 
                    ? $account->last_password_change->diffInDays(now())
                    : 999;
                
                return [
                    'type' => 'old_password',
                    'priority' => $daysSinceChange > 180 ? 'high' : 'medium',
                    'service' => $account->service_name,
                    'days_old' => $daysSinceChange,
                    'message' => "Password untuk {$account->service_name} sudah {$daysSinceChange} hari tidak diganti",
                ];
            });
    }

    /**
     * Get emergency fund alert.
     */
    private function getEmergencyFundAlert(User $user): ?array
    {
        $emergencyFund = $user->emergencyFund;
        
        if (!$emergencyFund) {
            return [
                'type' => 'emergency_fund_missing',
                'priority' => 'high',
                'message' => 'Anda belum memiliki dana darurat. Segera buat target dana darurat!',
            ];
        }
        
        if ($emergencyFund->progress_percentage < 25) {
            return [
                'type' => 'emergency_fund_low',
                'priority' => 'high',
                'progress' => $emergencyFund->progress_percentage,
                'message' => 'Dana darurat Anda masih sangat rendah (' . round($emergencyFund->progress_percentage, 1) . '%). Prioritaskan untuk menabung!',
            ];
        }
        
        if ($emergencyFund->progress_percentage < 50) {
            return [
                'type' => 'emergency_fund_medium',
                'priority' => 'medium',
                'progress' => $emergencyFund->progress_percentage,
                'message' => 'Dana darurat Anda sudah ' . round($emergencyFund->progress_percentage, 1) . '%. Terus tingkatkan!',
            ];
        }
        
        return null;
    }

    /**
     * Get notification count.
     */
    public function getNotificationCount(User $user): int
    {
        $notifications = $this->getPendingNotifications($user);
        
        $count = 0;
        foreach ($notifications as $category) {
            if (is_array($category)) {
                $count += count($category);
            } else {
                $count += 1;
            }
        }
        
        return $count;
    }

    /**
     * Get high priority notifications only.
     */
    public function getHighPriorityNotifications(User $user): array
    {
        $allNotifications = $this->getPendingNotifications($user);
        $highPriority = [];
        
        foreach ($allNotifications as $category => $items) {
            if (is_array($items) && isset($items[0])) {
                $filtered = collect($items)->filter(function ($item) {
                    return ($item['priority'] ?? '') === 'high';
                });
                
                if ($filtered->isNotEmpty()) {
                    $highPriority[$category] = $filtered->values()->toArray();
                }
            } elseif (isset($items['priority']) && $items['priority'] === 'high') {
                $highPriority[$category] = $items;
            }
        }
        
        return $highPriority;
    }

    /**
     * Mark task reminder as sent.
     */
    public function markTaskReminderSent(Task $task): bool
    {
        // Clear reminder so it won't show again
        $task->reminder_at = null;
        return $task->save();
    }
}
