<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalAccount;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;

class DigitalAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = DigitalAccount::where('user_id', auth()->id())->get();
        
        $incomeSources = $accounts->where('type', 'income_source');
        $wallets = $accounts->where('type', 'wallet');

        $totalBalance = $accounts->reduce(function ($carry, $item) {
            $rate = ($item->currency == 'USD') ? 16000 : 1; 
            return $carry + ($item->current_balance * $rate);
        }, 0);

        return view('digital_accounts.index', compact('incomeSources', 'wallets', 'totalBalance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'platform_name' => 'required|string|max:255',
            'current_balance' => 'required|numeric|min:0',
            'website_url' => 'nullable|url',
        ]);

        DigitalAccount::create([
            'user_id' => auth()->id(),
            'platform_name' => $request->platform_name,
            'current_balance' => $request->current_balance,
            'website_url' => $request->website_url,
        ]);

        return back()->with('success', 'Akun digital berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DigitalAccount $digitalAccount)
    {
        // Owner check
        // Owner check (Allow if own OR system account)
        if ($digitalAccount->user_id !== auth()->id() && !is_null($digitalAccount->user_id)) abort(403);

        $request->validate([
            'current_balance' => 'required|numeric|min:0',
            'website_url' => 'nullable|url',
        ]);

        $digitalAccount->update([
            'current_balance' => $request->current_balance,
            'website_url' => $request->website_url,
        ]);

        return back()->with('success', 'Saldo berhasil diperbarui.');
    }

    /**
     * Withdraw balance to main wallet (Record as Income).
     */
    public function withdraw(Request $request, DigitalAccount $digitalAccount)
    {
        if ($digitalAccount->user_id !== auth()->id() && !is_null($digitalAccount->user_id)) abort(403);

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:' . $digitalAccount->current_balance,
        ]);

        DB::transaction(function () use ($request, $digitalAccount) {
            $amount = $request->amount;

            // 1. Deduct from Digital Account
            $digitalAccount->decrement('current_balance', $amount);

            // 2. Add to Main Transactions (Income)
            FinancialTransaction::create([
                'user_id' => auth()->id(),
                'type' => 'income',
                'amount' => $amount,
                'category_id' => null, // Or a specific "Digital Income" category if exists
                'transaction_date' => now(),
                'source' => $digitalAccount->platform_name,
                'description' => 'Penarikan dari ' . $digitalAccount->platform_name,
            ]);
        });

        return back()->with('success', 'Penarikan berhasil! Saldo masuk ke kas utama.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DigitalAccount $digitalAccount)
    {
        if ($digitalAccount->user_id !== auth()->id()) abort(403);
        $digitalAccount->delete();
        return back()->with('success', 'Akun digital dihapus.');
    }
}
