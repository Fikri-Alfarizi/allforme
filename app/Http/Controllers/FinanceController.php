<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\ExpenseCategory;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinanceController extends Controller
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Display all financial transactions.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $type = $request->get('type'); // all, income, expense

        $query = $user->financialTransactions()->with('category');

        // Filter by type
        if ($type === 'income') {
            $query->income();
        } elseif ($type === 'expense') {
            $query->expense();
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(20);

        // Get summary for current month
        $summary = $this->financeService->getCurrentMonthSummary($user);

        // Get categories for filter
        $categories = ExpenseCategory::forUser($user->id)->get();

        return view('finance.index', compact('transactions', 'summary', 'categories', 'type'));
    }

    /**
     * Show form to create new transaction.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'expense');
        $categories = ExpenseCategory::forUser(auth()->id())->get();

        return view('finance.create', compact('type', 'categories'));
    }

    /**
     * Store new transaction.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:expense_categories,id',
            'source' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_period' => 'nullable|in:daily,weekly,monthly,yearly',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $transaction = FinancialTransaction::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'is_recurring' => $request->is_recurring ?? false,
            'recurring_period' => $request->recurring_period,
            'tags' => $request->tags,
        ]);

        return redirect()->route('finance.index')
            ->with('success', 'Transaksi berhasil ditambahkan!');
    }

    /**
     * Show form to edit transaction (redirect to index with modal).
     */
    public function edit(FinancialTransaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        // Redirect to finance index page
        // The modal will be opened via JavaScript on page load
        return redirect()->route('finance.index')
            ->with('edit_transaction_id', $transaction->id);
    }

    /**
     * Update transaction.
     */
    public function update(Request $request, FinancialTransaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:expense_categories,id',
            'source' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_period' => 'nullable|in:daily,weekly,monthly,yearly',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $transaction->update([
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'is_recurring' => $request->is_recurring ?? false,
            'recurring_period' => $request->recurring_period,
            'tags' => $request->tags,
        ]);

        return redirect()->route('finance.index')
            ->with('success', 'Transaksi berhasil diupdate!');
    }

    /**
     * Get transaction data (for AJAX).
     */
    public function show(FinancialTransaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($transaction);
    }

    /**
     * Delete transaction.
     */
    public function destroy(FinancialTransaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('finance.index')
            ->with('success', 'Transaksi berhasil dihapus!');
    }

    /**
     * Get analytics data.
     */
    public function analytics(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'monthly'); // weekly, monthly, yearly

        $months = match($period) {
            'weekly' => 1,
            'yearly' => 12,
            default => 6,
        };

        $cashflowData = $this->financeService->getCashflowData($user, $months);
        $expensesByCategory = $this->financeService->getExpensesByCategory($user);
        $healthScore = $this->financeService->getFinancialHealthScore($user);

        return view('finance.analytics', compact('cashflowData', 'expensesByCategory', 'healthScore', 'period'));
    }
}
