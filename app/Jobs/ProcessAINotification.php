<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Notification;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAINotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        try {
            // For now, create a simple notification without AI processing
            // This is to test the notification system first
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'ai_daily_insight',
                'title' => '🤖 Insight Keuangan Harian',
                'message' => 'Halo ' . $this->user->name . '! Sistem notifikasi AI otomatis sudah aktif. Analisis keuangan harian Anda akan muncul di sini setiap hari jam 8 pagi. 💰',
                'data' => json_encode([
                    'test' => true,
                    'generated_at' => now()->toDateTimeString(),
                ]),
                'is_read' => false,
            ]);
            
            Log::info("Daily AI notification created for user {$this->user->id}");
            
        } catch (\Exception $e) {
            Log::error("Exception in ProcessAINotification for user {$this->user->id}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Generate daily insight prompt
     */
    private function generateDailyInsightPrompt(array $financialData): string
    {
        $metrics = $financialData['financial_metrics'];
        
        return "Berikan insight keuangan harian yang singkat dan actionable (maksimal 3-4 kalimat) berdasarkan data berikut:\n\n" .
               "- Total Income: Rp " . number_format($metrics['total_income'], 0, ',', '.') . "\n" .
               "- Total Expense: Rp " . number_format($metrics['total_expense'], 0, ',', '.') . "\n" .
               "- Savings Rate: " . number_format($metrics['savings_rate'], 1) . "%\n" .
               "- Emergency Fund Progress: " . number_format($metrics['emergency_fund_progress'], 1) . "%\n" .
               "- Financial Health Score: " . $metrics['financial_health_score']['total_score'] . "/100\n\n" .
               "Fokus pada 1-2 rekomendasi paling penting untuk hari ini. Gunakan emoji yang relevan.";
    }

    /**
     * Format AI response for notification
     */
    private function formatAIResponse(string $response): string
    {
        // Limit to 300 characters for notification
        if (strlen($response) > 300) {
            return substr($response, 0, 297) . '...';
        }
        
        return $response;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessAINotification failed permanently for user {$this->user->id}", [
            'message' => $exception->getMessage(),
        ]);
    }
}
