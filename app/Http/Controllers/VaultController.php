<?php

namespace App\Http\Controllers;

use App\Models\AccountVault;
use App\Services\VaultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VaultController extends Controller
{
    protected $vaultService;

    public function __construct(VaultService $vaultService)
    {
        $this->vaultService = $vaultService;
    }

    /**
     * Display all vault accounts.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $type = $request->get('type');
        $search = $request->get('search');

        if ($search) {
            $accounts = $this->vaultService->searchAccounts($user, $search);
        } elseif ($type) {
            $accounts = $this->vaultService->getAccountsByType($user, $type);
        } else {
            $accounts = $this->vaultService->getAllAccounts($user);
        }

        $statistics = $this->vaultService->getVaultStatistics($user);
        $oldPasswords = $this->vaultService->getAccountsWithOldPasswords($user);

        return view('vault.index', compact('accounts', 'statistics', 'oldPasswords', 'type', 'search'));
    }

    /**
     * Show form to create new account.
     */
    public function create()
    {
        return view('vault.create');
    }

    /**
     * Store new account in vault.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_type' => 'required|in:email,game,social_media,website,api,other',
            'service_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->vaultService->createAccount(
            auth()->user(),
            $request->account_type,
            $request->service_name,
            $request->username,
            $request->password,
            $request->email,
            $request->notes,
            $request->url
        );

        return redirect()->route('vault.index')
            ->with('success', 'Akun berhasil ditambahkan ke vault!');
    }

    /**
     * Show account details.
     */
    public function show(AccountVault $vault)
    {
        // Ensure user owns this account
        if ($vault->user_id !== auth()->id()) {
            abort(403);
        }

        return view('vault.show', compact('vault'));
    }

    /**
     * Show form to edit account.
     */
    public function edit(AccountVault $vault)
    {
        // Ensure user owns this account
        if ($vault->user_id !== auth()->id()) {
            abort(403);
        }

        return view('vault.edit', compact('vault'));
    }

    /**
     * Update account in vault.
     */
    public function update(Request $request, AccountVault $vault)
    {
        // Ensure user owns this account
        if ($vault->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'account_type' => 'required|in:email,game,social_media,website,api,other',
            'service_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
            'url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $vault->update([
            'account_type' => $request->account_type,
            'service_name' => $request->service_name,
            'username' => $request->username,
            'email' => $request->email,
            'notes' => $request->notes,
            'url' => $request->url,
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $vault->updatePassword($request->password);
        }

        return redirect()->route('vault.index')
            ->with('success', 'Akun berhasil diupdate!');
    }

    /**
     * Delete account from vault.
     */
    public function destroy(AccountVault $vault)
    {
        // Ensure user owns this account
        if ($vault->user_id !== auth()->id()) {
            abort(403);
        }

        $this->vaultService->deleteAccount($vault);

        return redirect()->route('vault.index')
            ->with('success', 'Akun berhasil dihapus dari vault!');
    }

    /**
     * Generate strong password.
     */
    public function generatePassword(Request $request)
    {
        $length = $request->get('length', 16);
        $password = $this->vaultService->generateStrongPassword($length);

        return response()->json([
            'password' => $password,
        ]);
    }

    /**
     * Check password strength.
     */
    public function checkStrength(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Password required'], 400);
        }

        $result = $this->vaultService->checkPasswordStrength($request->password);

        return response()->json($result);
    }

    /**
     * Export vault data.
     */
    public function export()
    {
        $user = auth()->user();
        $data = $this->vaultService->exportVault($user);

        $filename = 'vault_export_' . date('Y-m-d_His') . '.json';

        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
