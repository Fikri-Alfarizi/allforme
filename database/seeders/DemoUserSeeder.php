<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@plfis.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create user settings
        \DB::table('settings')->insert([
            'user_id' => $user->id,
            'currency' => 'IDR',
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'theme' => 'light',
            'notification_enabled' => true,
            'ai_enabled' => true,
            'vault_timeout_minutes' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create emergency fund
        \DB::table('emergency_funds')->insert([
            'user_id' => $user->id,
            'target_amount' => 15000000, // 15 juta
            'current_amount' => 2500000, // 2.5 juta
            'monthly_expense_base' => 2500000,
            'target_months' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add sample income
        \DB::table('financial_transactions')->insert([
            [
                'user_id' => $user->id,
                'type' => 'income',
                'category_id' => null,
                'amount' => 5000000,
                'source' => 'Gaji Bulanan',
                'description' => 'Gaji bulan ini',
                'transaction_date' => now()->format('Y-m-d'),
                'is_recurring' => true,
                'recurring_period' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'type' => 'income',
                'category_id' => null,
                'amount' => 1500000,
                'source' => 'Freelance',
                'description' => 'Project website',
                'transaction_date' => now()->subDays(5)->format('Y-m-d'),
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add sample expenses
        $kebutuhanPokok = \DB::table('expense_categories')->where('name', 'Kebutuhan Pokok')->first();
        $transport = \DB::table('expense_categories')->where('name', 'Transport')->first();
        $internet = \DB::table('expense_categories')->where('name', 'Internet & Komunikasi')->first();

        \DB::table('financial_transactions')->insert([
            [
                'user_id' => $user->id,
                'type' => 'expense',
                'category_id' => $kebutuhanPokok->id,
                'amount' => 500000,
                'source' => 'Supermarket',
                'description' => 'Belanja bulanan',
                'transaction_date' => now()->subDays(2)->format('Y-m-d'),
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'type' => 'expense',
                'category_id' => $transport->id,
                'amount' => 200000,
                'source' => 'Bensin',
                'description' => 'Isi bensin',
                'transaction_date' => now()->subDays(1)->format('Y-m-d'),
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'type' => 'expense',
                'category_id' => $internet->id,
                'amount' => 350000,
                'source' => 'Provider Internet',
                'description' => 'Bayar internet bulanan',
                'transaction_date' => now()->format('Y-m-d'),
                'is_recurring' => true,
                'recurring_period' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add recurring expenses
        \DB::table('recurring_expenses')->insert([
            [
                'user_id' => $user->id,
                'category_id' => $internet->id,
                'name' => 'Internet Bulanan',
                'amount' => 350000,
                'period' => 'monthly',
                'next_due_date' => now()->addMonth()->format('Y-m-d'),
                'is_active' => true,
                'reminder_days_before' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'category_id' => $kebutuhanPokok->id,
                'name' => 'Pulsa',
                'amount' => 50000,
                'period' => 'monthly',
                'next_due_date' => now()->addDays(15)->format('Y-m-d'),
                'is_active' => true,
                'reminder_days_before' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add sample notes
        \DB::table('notes')->insert([
            [
                'user_id' => $user->id,
                'title' => 'Target Keuangan 2024',
                'content' => 'Tahun ini saya ingin mencapai dana darurat 15 juta dan mulai investasi.',
                'tags' => json_encode(['keuangan', 'target']),
                'is_pinned' => true,
                'color' => '#FFD700',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Ide Project',
                'content' => 'Membuat aplikasi manajemen keuangan pribadi dengan AI.',
                'tags' => json_encode(['ide', 'project']),
                'is_pinned' => false,
                'color' => '#87CEEB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add sample tasks
        \DB::table('tasks')->insert([
            [
                'user_id' => $user->id,
                'title' => 'Review pengeluaran bulan ini',
                'description' => 'Cek apakah ada pengeluaran yang bisa dikurangi',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(3),
                'reminder_at' => now()->addDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Bayar internet',
                'description' => 'Jangan lupa bayar tagihan internet',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(7),
                'reminder_at' => now()->addDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
