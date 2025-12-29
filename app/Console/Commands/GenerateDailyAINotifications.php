<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAINotification;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateDailyAINotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:daily-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily AI-powered financial notifications for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily AI notification generation...');

        // Get all active users
        $users = User::all();

        $this->info("Found {$users->count()} users to process.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Dispatch job to queue for async processing
            ProcessAINotification::dispatch($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Daily AI notifications queued successfully!');

        return Command::SUCCESS;
    }
}
