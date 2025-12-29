<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Services\FinanceService;
use App\Services\EmergencyFundService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $financeService;
    protected $emergencyFundService;
    protected $notificationService;

    public function __construct(
        FinanceService $financeService,
        EmergencyFundService $emergencyFundService,
        NotificationService $notificationService
    ) {
        $this->financeService = $financeService;
        $this->emergencyFundService = $emergencyFundService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display dashboard home page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get current month summary
        $summary = $this->financeService->getCurrentMonthSummary($user);

        // Get emergency fund data
        $emergencyFund = $user->emergencyFund;
        $emergencyFundData = null;
        if ($emergencyFund) {
            $emergencyFundData = $this->emergencyFundService->getProgressReport($emergencyFund);
        }

        // Get financial health score
        $healthScore = $this->financeService->getFinancialHealthScore($user);

        // Get cashflow data for chart (default: monthly)
        $cashflowData = $this->financeService->getCashflowData($user, 'monthly');

        // Get expenses by category
        $expensesByCategory = $this->financeService->getExpensesByCategory(
            $user,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        // Get notifications
        $notifications = $this->notificationService->getPendingNotifications($user);
        $notificationCount = $this->notificationService->getNotificationCount($user);

        // Get active tasks (pending or in_progress)
        $todayTasks = $user->tasks()
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();

        // Get pinned notes
        $pinnedNotes = $user->notes()
            ->where('is_pinned', true)
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        // Get recent notes
        $recentNotes = $user->notes()
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate monthly change (Net Income comparing to last month)
        $monthlyChange = 0;
        if (count($cashflowData) >= 2) {
            $currentMonth = end($cashflowData);
            $lastMonth = prev($cashflowData);
            
            if ($lastMonth['net'] != 0) {
                $monthlyChange = (($currentMonth['net'] - $lastMonth['net']) / abs($lastMonth['net'])) * 100;
            } else if ($currentMonth['net'] > 0) {
                $monthlyChange = 100;
            }
        }

        // Get latest AI Insight (from logs)
        $latestAiLog = $user->aiLogs()
            ->whereNotNull('response')
            ->latest()
            ->first();

        // Get Data for Quick Action Modals
        $categories = ExpenseCategory::forUser($user->id)->get();
        
        $allTags = $user->notes()
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        // Get Total Digital Assets
        $digitalAccounts = \App\Models\DigitalAccount::where('user_id', $user->id)->get();

        $totalDigitalAssets = $digitalAccounts->reduce(function ($carry, $item) {
            $rate = ($item->currency == 'USD') ? 16000 : 1; 
            return $carry + ($item->current_balance * $rate);
        }, 0);

        return view('dashboard.index', compact(
            'summary',
            'emergencyFundData',
            'healthScore',
            'cashflowData',
            'expensesByCategory',
            'notifications',
            'notificationCount',
            'todayTasks',
            'pinnedNotes',
            'recentNotes',
            'monthlyChange',
            'latestAiLog',
            'categories',
            'allTags',
            'totalDigitalAssets'
        ));
    }

    /**
     * Get cashflow data for AJAX chart updates.
     */
    public function getCashflowData(Request $request)
    {
        $period = $request->query('period', 'monthly');
        $user = auth()->user();
        
        $data = $this->financeService->getCashflowData($user, $period);
        
        return response()->json($data);
    }
}
