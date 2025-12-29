<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationAnalyzer;
use App\Services\FinanceService;
use App\Notifications\FinancialAlert;

class AnalyzeFinancialHealth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:analyze-health';

    /**
     * The console command description.
     */
    protected $description = 'Analyze users financial health and send intelligent notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting financial health analysis...');
        
        $users = User::all();
        $totalNotifications = 0;
        
        foreach ($users as $user) {
            $this->line("Analyzing user: {$user->name} (ID: {$user->id})");
            
            try {
                $financeService = new FinanceService();
                $analyzer = new NotificationAnalyzer($financeService);
                
                $notifications = $analyzer->analyzeUser($user);
                
                foreach ($notifications as $notificationData) {
                    // Send notification
                    $user->notify(new FinancialAlert($notificationData));
                    $totalNotifications++;
                    
                    $this->info("  ✓ Sent: {$notificationData['title']}");
                }
                
                if (empty($notifications)) {
                    $this->line("  No alerts for this user");
                }
                
            } catch (\Exception $e) {
                $this->error("  Error analyzing user {$user->id}: " . $e->getMessage());
            }
        }
        
        $this->info("\nAnalysis complete!");
        $this->info("Total notifications sent: {$totalNotifications}");
        
        return Command::SUCCESS;
    }
}
