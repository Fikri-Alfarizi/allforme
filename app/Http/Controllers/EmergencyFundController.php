<?php

namespace App\Http\Controllers;

use App\Models\EmergencyFund;
use App\Services\EmergencyFundService;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmergencyFundController extends Controller
{
    protected $emergencyFundService;
    protected $financeService;

    public function __construct(
        EmergencyFundService $emergencyFundService,
        FinanceService $financeService
    ) {
        $this->emergencyFundService = $emergencyFundService;
        $this->financeService = $financeService;
    }

    /**
     * Display emergency fund dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $fund = $user->emergencyFund;

        if (!$fund) {
            // Auto-initialize with average monthly expenses
            $averageExpenses = $this->financeService->getAverageMonthlyExpenses($user, 3);
            
            if ($averageExpenses > 0) {
                $fund = $this->emergencyFundService->initialize($user, $averageExpenses, 6);
            }
        }

        $progressReport = $fund ? $this->emergencyFundService->getProgressReport($fund) : null;
        $recommendations = $fund ? $this->emergencyFundService->getAIRecommendations($fund) : [];

        return view('emergency-fund.index', compact('fund', 'progressReport', 'recommendations'));
    }

    /**
     * Initialize or update emergency fund target.
     */
    public function updateTarget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monthly_expense_base' => 'required|numeric|min:0',
            'target_months' => 'required|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $fund = $user->emergencyFund;

        if ($fund) {
            $this->emergencyFundService->updateTarget(
                $fund,
                $request->monthly_expense_base
            );
            $fund->target_months = $request->target_months;
            $fund->save();
        } else {
            $fund = $this->emergencyFundService->initialize(
                $user,
                $request->monthly_expense_base,
                $request->target_months
            );
        }

        return back()->with('success', 'Target dana darurat berhasil diupdate!');
    }

    /**
     * Add contribution to emergency fund.
     */
    public function addContribution(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $fund = $user->emergencyFund;

        if (!$fund) {
            return back()->with('error', 'Silakan set target dana darurat terlebih dahulu.');
        }

        $this->emergencyFundService->addContribution($fund, $request->amount, $request->note);

        return back()->with('success', 'Kontribusi berhasil ditambahkan!');
    }

    /**
     * Withdraw from emergency fund.
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $fund = $user->emergencyFund;

        if (!$fund) {
            return back()->with('error', 'Dana darurat tidak ditemukan.');
        }

        if ($request->amount > $fund->current_amount) {
            return back()->with('error', 'Saldo dana darurat tidak mencukupi.');
        }

        $this->emergencyFundService->withdraw($fund, $request->amount, $request->reason);

        return back()->with('success', 'Penarikan dana berhasil!');
    }

    /**
     * Auto-update monthly expense base from actual data.
     */
    public function autoUpdate()
    {
        $user = auth()->user();
        
        $result = $this->emergencyFundService->autoUpdateMonthlyExpenseBase(
            $user,
            $this->financeService
        );

        if ($result) {
            return back()->with('success', 'Target dana darurat berhasil diupdate berdasarkan pengeluaran aktual!');
        }

        return back()->with('error', 'Gagal mengupdate target.');
    }
}
