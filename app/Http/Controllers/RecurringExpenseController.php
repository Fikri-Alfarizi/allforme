<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecurringExpenseController extends Controller
{
    /**
     * Display all recurring expenses.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', 'active');

        $query = $user->recurringExpenses()->with('category');

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $expenses = $query->orderBy('next_due_date', 'asc')->get();

        // Get upcoming and overdue
        $upcoming = $user->recurringExpenses()->upcoming(7)->get();
        $overdue = $user->recurringExpenses()->overdue()->get();

        // Calculate total monthly cost
        $monthlyTotal = $user->recurringExpenses()
            ->active()
            ->where('period', 'monthly')
            ->sum('amount');

        $categories = ExpenseCategory::forUser($user->id)->get();

        return view('recurring.index', compact('expenses', 'upcoming', 'overdue', 'monthlyTotal', 'categories', 'status'));
    }

    /**
     * Show form to create new recurring expense.
     */
    public function create()
    {
        $categories = ExpenseCategory::forUser(auth()->id())->get();
        return view('recurring.create', compact('categories'));
    }

    /**
     * Store new recurring expense.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:expense_categories,id',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'next_due_date' => 'required|date',
            'reminder_days_before' => 'nullable|integer|min:0|max:30',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        RecurringExpense::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'period' => $request->period,
            'next_due_date' => $request->next_due_date,
            'reminder_days_before' => $request->reminder_days_before,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('recurring.index')
            ->with('success', 'Pengeluaran rutin berhasil ditambahkan!');
    }

    /**
     * Show form to edit recurring expense.
     */
    public function edit(RecurringExpense $recurring)
    {
        // Ensure user owns this expense
        if ($recurring->user_id !== auth()->id()) {
            abort(403);
        }

        $categories = ExpenseCategory::forUser(auth()->id())->get();
        return view('recurring.edit', compact('recurring', 'categories'));
    }

    /**
     * Update recurring expense.
     */
    public function update(Request $request, RecurringExpense $recurring)
    {
        // Ensure user owns this expense
        if ($recurring->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:expense_categories,id',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'next_due_date' => 'required|date',
            'reminder_days_before' => 'nullable|integer|min:0|max:30',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $recurring->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'period' => $request->period,
            'next_due_date' => $request->next_due_date,
            'reminder_days_before' => $request->reminder_days_before,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('recurring.index')
            ->with('success', 'Pengeluaran rutin berhasil diupdate!');
    }

    /**
     * Delete recurring expense.
     */
    public function destroy(RecurringExpense $recurring)
    {
        // Ensure user owns this expense
        if ($recurring->user_id !== auth()->id()) {
            abort(403);
        }

        $recurring->delete();

        return redirect()->route('recurring.index')
            ->with('success', 'Pengeluaran rutin berhasil dihapus!');
    }

    /**
     * Mark recurring expense as paid and update next due date.
     */
    public function markAsPaid(RecurringExpense $recurring)
    {
        // Ensure user owns this expense
        if ($recurring->user_id !== auth()->id()) {
            abort(403);
        }

        $recurring->markAsPaid();

        return back()->with('success', 'Pembayaran dicatat! Next due date: ' . $recurring->next_due_date->format('d M Y'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(RecurringExpense $recurring)
    {
        // Ensure user owns this expense
        if ($recurring->user_id !== auth()->id()) {
            abort(403);
        }

        $recurring->is_active = !$recurring->is_active;
        $recurring->save();

        return back()->with('success', $recurring->is_active ? 'Diaktifkan!' : 'Dinonaktifkan!');
    }
}
