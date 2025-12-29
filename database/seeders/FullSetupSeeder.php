<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DigitalAccount;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FullSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            echo "No user found. Please register first.\n";
            return;
        }

        // 1. Seed Expense Categories
        $categories = [
            ['name' => 'Gaji', 'icon' => 'fa-money-bill-wave', 'color' => '#10b981'],
            ['name' => 'Makanan', 'icon' => 'fa-utensils', 'color' => '#f59e0b'],
            ['name' => 'Transportasi', 'icon' => 'fa-bus', 'color' => '#3b82f6'],
            ['name' => 'Tagihan', 'icon' => 'fa-file-invoice-dollar', 'color' => '#ef4444'],
            ['name' => 'Hiburan', 'icon' => 'fa-gamepad', 'color' => '#8b5cf6'],
            ['name' => 'Kesehatan', 'icon' => 'fa-heartbeat', 'color' => '#ec4899'],
            ['name' => 'Belanja', 'icon' => 'fa-shopping-bag', 'color' => '#f97316'],
            ['name' => 'Pendidikan', 'icon' => 'fa-graduation-cap', 'color' => '#14b8a6'],
            ['name' => 'Investasi', 'icon' => 'fa-chart-line', 'color' => '#6366f1'],
            ['name' => 'Lainnya', 'icon' => 'fa-ellipsis-h', 'color' => '#64748b'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['name' => $cat['name'], 'user_id' => $user->id],
                [
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_system' => false
                ]
            );
        }
        echo "Categories seeded.\n";

        // 2. Seed Digital Accounts (Passive Income)
        $incomeSources = [
            ['name' => 'ShrinkMe.io', 'url' => 'https://shrinkme.io', 'currency' => 'USD'],
            ['name' => 'SafelinkU', 'url' => 'https://safelinku.com', 'currency' => 'USD'],
            ['name' => 'Saweria', 'url' => 'https://saweria.co', 'currency' => 'IDR'],
            ['name' => 'YouTube', 'url' => 'https://youtube.com', 'currency' => 'IDR'],
            ['name' => 'Google Ads', 'url' => 'https://ads.google.com', 'currency' => 'IDR'],
        ];

        foreach ($incomeSources as $source) {
            DigitalAccount::updateOrCreate(
                ['platform_name' => $source['name'], 'user_id' => $user->id],
                [
                    'type' => 'income_source',
                    'current_balance' => 0,
                    'currency' => $source['currency'],
                    'website_url' => $source['url'],
                ]
            );
        }
        echo "Passive Income Sources seeded.\n";

        // 3. Seed Digital Wallets
        $wallets = [
            ['name' => 'Dana', 'url' => 'https://dana.id'],
            ['name' => 'PayPal', 'url' => 'https://paypal.com', 'currency' => 'USD'], // PayPal is a wallet
            ['name' => 'OVO', 'url' => 'https://ovo.id'],
            ['name' => 'ShopeePay', 'url' => 'https://shopeepay.co.id'],
            ['name' => 'Nanovest', 'url' => 'https://nanovest.io'],
        ];

        foreach ($wallets as $wallet) {
            DigitalAccount::updateOrCreate(
                ['platform_name' => $wallet['name'], 'user_id' => $user->id],
                [
                    'type' => 'wallet',
                    'current_balance' => 0,
                    'currency' => $wallet['currency'] ?? 'IDR',
                    'website_url' => $wallet['url'],
                ]
            );
        }
        echo "Digital Wallets seeded.\n";
    }
}
