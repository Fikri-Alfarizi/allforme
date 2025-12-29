<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;
use App\Models\ExpenseCategory;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoiceCommandController extends Controller
{
    protected $taskService;
    protected $emergencyFundService;

    public function __construct(
        AIService $aiService,
        \App\Services\TaskService $taskService,
        \App\Services\EmergencyFundService $emergencyFundService
    ) {
        $this->aiService = $aiService;
        $this->taskService = $taskService;
        $this->emergencyFundService = $emergencyFundService;
    }

    public function process(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:1000',
            ]);

            $text = $request->input('text');
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan. Silakan login kembali.',
                ], 401);
            }

            Log::info("Voice Command Request", [
                'user_id' => $user->id,
                'text' => $text
            ]);

            // fetch categories for context
            $categories = ExpenseCategory::forUser($user->id)->get(['id', 'name'])->toArray();

            // Call AI Service to parse
            $result = $this->aiService->parseVoiceCommand($user, $text, $categories);

            if (!$result['success']) {
                Log::warning("Voice command parsing failed", [
                    'user_id' => $user->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses dengan AI: ' . ($result['error'] ?? 'Unknown error'),
                ], 400);
            }

            $intentData = $result['data'];
            $intent = $intentData['intent'] ?? 'unknown';
            $data = $intentData['data'] ?? [];

            DB::beginTransaction();
            try {
                $message = 'Perintah berhasil diproses!';
                $responseData = [];

                switch ($intent) {
                    case 'transaction':
                        $transactions = is_array($data) ? $data : [$data];
                    foreach ($transactions as $t) {
                        $type = strtolower($t['type'] ?? 'expense');
                        if (!in_array($type, ['income', 'expense'])) $type = 'expense';
                        
                        FinancialTransaction::create([
                            'user_id' => $user->id,
                            'type' => $type,
                            'amount' => abs($t['amount']),
                            'description' => $t['description'] ?? $text,
                            'category_id' => $t['category_id'] ?? null,
                            'transaction_date' => $t['date'] ?? now()->format('Y-m-d'),
                            'source' => 'Voice AI',
                        ]);
                    }
                    $message = count($transactions) . ' transaksi berhasil ditambahkan.';
                    break;

                case 'task':
                    $tasks = is_array($data) && isset($data[0]) ? $data : [$data];
                    foreach ($tasks as $t) {
                        $this->taskService->createTask($user, [
                            'title' => $t['title'],
                            'description' => $t['description'] ?? null,
                            'due_date' => $t['due_date'] ?? now(),
                            'priority' => $t['priority'] ?? 'medium',
                        ]);
                    }
                    $message = count($tasks) . ' tugas berhasil dibuat.';
                    break;

                case 'emergency_fund':
                    $fund = $user->emergencyFund;
                     // Auto-create if not exists
                    if (!$fund) {
                        $fund = $this->emergencyFundService->initialize($user, 0); 
                    }

                    $action = $data['action'] ?? 'set_target';

                    if ($action === 'set_target') {
                        $this->emergencyFundService->setTargetAmount($fund, $data['amount'] ?? 0);
                        $message = 'Target dana darurat diperbarui.';
                    } elseif ($action === 'add_contribution') {
                        $this->emergencyFundService->addContribution($fund, $data['amount'] ?? 0);
                        $message = 'Berhasil menabung ke dana darurat.';
                    } elseif ($action === 'withdraw') {
                        $this->emergencyFundService->withdraw($fund, $data['amount'] ?? 0);
                        $message = 'Berhasil menarik dari dana darurat.';
                    } elseif ($action === 'calculate_recommendation') {
                        // Calculate 6 months of expenses
                        $last3MonthsExpense = FinancialTransaction::where('user_id', $user->id)
                            ->where('type', 'expense')
                            ->where('transaction_date', '>=', now()->subMonths(3))
                            ->sum('amount');
                        
                        // Safety check to avoid zero division if new user
                        $monthlyAvg = $last3MonthsExpense > 0 ? ($last3MonthsExpense / 3) : 0;
                        
                        // Fallback to Recurring Expenses if transaction history is low
                        if ($monthlyAvg < 100000) {
                             $monthlyRecurring = \App\Models\RecurringExpense::where('user_id', $user->id)
                                ->where('is_active', true)
                                ->sum('amount');
                             $monthlyAvg = max($monthlyAvg, $monthlyRecurring);
                        }

                        $recommendedTarget = $monthlyAvg * 6;
                        
                        // Only update if we have a valid recommendation
                        if ($recommendedTarget < 500000) {
                             $message = 'Belum bisa menghitung rekomendasi karena data pengeluaran Anda masih sedikit atau nol. Silakan catat transaksi pengeluaran (Expenses) setidaknya untuk bulan ini, lalu coba lagi.';
                        } else {
                             $this->emergencyFundService->setTargetAmount($fund, $recommendedTarget);
                             $message = 'Target Dana Darurat otomatis diatur ke Rp ' . number_format($recommendedTarget, 0, ',', '.') . ' (6x pengeluaran bulanan).';
                        }
                    }
                    break;
                
                case 'financial_goal':
                    $action = $data['action'] ?? 'create';
                    $title = $data['title'] ?? 'Goal';
                    $amount = $data['amount'] ?? 0;
                    
                    if ($action === 'create') {
                        \App\Models\FinancialGoal::create([
                            'user_id' => $user->id,
                            'title' => $title,
                            'target_amount' => $amount,
                            'current_amount' => 0,
                            'target_date' => $data['due_date'] ?? null,
                            'status' => 'active',
                        ]);
                        $message = "Tujuan keuangan '{$title}' berhasil dibuat dengan target Rp " . number_format($amount);
                    } elseif ($action === 'add_contribution') {
                        $goal = \App\Models\FinancialGoal::where('user_id', $user->id)
                            ->where('title', 'like', "%{$title}%")
                            ->where('status', 'active')
                            ->first();
                            
                        if (!$goal) throw new \Exception("Tujuan '{$title}' tidak ditemukan.");
                        
                        $goal->increment('current_amount', $amount);
                        $message = "Berhasil menabung Rp " . number_format($amount) . " untuk '{$goal->title}'. Terkumpul: Rp " . number_format($goal->current_amount);
                        
                    } elseif ($action === 'complete') {
                        $goal = \App\Models\FinancialGoal::where('user_id', $user->id)
                            ->where('title', 'like', "%{$title}%")
                            ->where('status', 'active')
                            ->first();

                        if (!$goal) throw new \Exception("Tujuan '{$title}' tidak ditemukan.");
                        
                        $finalAmount = $amount > 0 ? $amount : $goal->target_amount;
                        
                        // 1. Mark Completed
                        $goal->update(['status' => 'completed', 'current_amount' => $finalAmount]);
                        
                        // 2. Create Expense
                        FinancialTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'expense',
                            'amount' => $finalAmount,
                            'description' => "Pembelian Goal: {$goal->title}",
                            'transaction_date' => now(),
                            'source' => 'Voice Goal Completion'
                        ]);
                        
                        // 3. AI Warning Logic
                        $monthSummary = FinancialTransaction::where('user_id', $user->id)
                            ->whereMonth('transaction_date', now()->month)
                            ->selectRaw('sum(case when type="income" then amount else 0 end) as income, sum(case when type="expense" then amount else 0 end) as expense')
                            ->first();
                            
                        $message = "Selamat! Tujuan '{$goal->title}' tercapai dan tercatat sebagai pengeluaran.";
                        
                        if ($monthSummary->expense > $monthSummary->income) {
                            $message .= "\n\n⚠️ PERINGATAN: Pengeluaran bulan ini sudah melebihi pendapatan! Harap berhemat.";
                        } elseif ($monthSummary->expense > ($monthSummary->income * 0.8)) {
                            $message .= "\n\n⚠️ Hati-hati, kamu sudah menghabiskan lebih dari 80% pendapatan bulan ini.";
                        }
                    }
                    break;
                case 'digital_income':
                    $platform = $data['platform'] ?? null;
                    if (!$platform) throw new \Exception('Nama platform tidak ditemukan.');
                    
                    $account = \App\Models\DigitalAccount::where(function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereNull('user_id');
                    })->where('platform_name', 'like', "%{$platform}%")->first();
                    if (!$account) {
                         // Auto create if not exists? Maybe dangerous via voice. Better just error or text match loosely.
                         throw new \Exception("Akun digital '{$platform}' tidak ditemukan.");
                    }

                    if (($data['action'] ?? 'update') === 'withdraw') {
                        // Withdraw Logic Reuse
                        $amount = $data['amount'] ?? 0;
                        if ($amount > $account->current_balance) throw new \Exception('Saldo tidak cukup.');
                        
                        $account->decrement('current_balance', $amount);
                        
                        FinancialTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'income',
                            'amount' => $amount,
                            'transaction_date' => now(),
                            'source' => $account->platform_name,
                            'description' => 'Penarikan Voice dari ' . $account->platform_name,
                        ]);
                        $message = "Berhasil withdraw {$amount} dari {$account->platform_name}.";
                    } else {
                        // Update Balance
                        $account->update(['current_balance' => $data['amount']]);
                        $message = "Saldo {$account->platform_name} diupdate menjadi {$data['amount']}.";
                    }
                    break;

                default:
                    throw new \Exception('Maaf, saya tidak mengerti perintah tersebut.');
            }

            DB::commit();

            Log::info('Voice command processed successfully', [
                'user_id' => $user->id,
                'intent' => $intent,
                'message' => $message
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Voice Command Error', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'intent' => $intent ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 400);
        }
        } catch (\Exception $e) {
            Log::error('Voice Command Validation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error validasi: ' . $e->getMessage(),
            ], 422);
        }
    }
}
