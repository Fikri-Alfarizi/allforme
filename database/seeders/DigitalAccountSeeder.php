<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DigitalAccount; // Pastikan model ini ada

class DigitalAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // --- Sumber Passive Income ---
            ['platform_name' => 'Shrinkme', 'type' => 'income_source', 'website_url' => 'https://shrinkme.io'],
            ['platform_name' => 'SafelinkU', 'type' => 'income_source', 'website_url' => 'https://safelinku.com'],
            ['platform_name' => 'Saweria', 'type' => 'income_source', 'website_url' => 'https://saweria.co'],
            ['platform_name' => 'YouTube', 'type' => 'income_source', 'website_url' => 'https://youtube.com'],
            ['platform_name' => 'Google AdSense', 'type' => 'income_source', 'website_url' => 'https://adsense.google.com'],

            // --- Dompet Digital ---
            ['platform_name' => 'Dana', 'type' => 'wallet', 'website_url' => 'https://dana.id'],
            ['platform_name' => 'PayPal', 'type' => 'wallet', 'website_url' => 'https://paypal.com'],
            ['platform_name' => 'OVO', 'type' => 'wallet', 'website_url' => 'https://ovo.id'],
            ['platform_name' => 'ShopeePay', 'type' => 'wallet', 'website_url' => 'https://shopee.co.id/shopee-pay'],
            ['platform_name' => 'Nanovest', 'type' => 'wallet', 'website_url' => 'https://nanovest.io'],
        ];

        foreach ($accounts as $accountData) {
            // Gunakan firstOrCreate untuk mencegah duplikasi data
            DigitalAccount::firstOrCreate(
                // Kondisi pencarian: cari berdasarkan nama platform
                ['platform_name' => $accountData['platform_name']],
                // Data yang akan dibuat jika tidak ditemukan:
                [
                    'type' => $accountData['type'],
                    'website_url' => $accountData['website_url'],
                    'user_id' => null,           // Tidak terikat ke user manapun (default sistem)
                    'current_balance' => 0,
                    'currency' => 'IDR',
                    'is_system' => true,          // Tandai ini sebagai akun bawaan sistem
                ]
            );
        }

        $this->command->info('Berhasil membuat data DigitalAccount default sistem.');
    }
}