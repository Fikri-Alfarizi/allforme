<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search across the application.
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $user = auth()->user();

        if (!$query) {
            return view('search.index', ['results' => [], 'query' => '']);
        }

        // Search Transactions
        $transactions = \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                  ->orWhere('source', 'like', "%{$query}%")
                  ->orWhere('amount', 'like', "%{$query}%");
            })
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Search Tasks
        $tasks = \App\Models\Task::where('user_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Search Notes
        $notes = \App\Models\Note::where('user_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('search.index', compact('transactions', 'tasks', 'notes', 'query'));
    }
}
