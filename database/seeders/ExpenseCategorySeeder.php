<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory; // Pastikan model ini sudah ada

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Expense Categories
        $expenseCategories = [
            ['name' => 'Makanan & Minuman', 'icon' => 'fas fa-utensils', 'color' => '#EF4444'], 
            ['name' => 'Transportasi', 'icon' => 'fas fa-car-side', 'color' => '#3B82F6'], 
            ['name' => 'Belanja', 'icon' => 'fas fa-shopping-bag', 'color' => '#8B5CF6'], 
            ['name' => 'Tagihan & Utilitas', 'icon' => 'fas fa-file-invoice-dollar', 'color' => '#F59E0B'], 
            ['name' => 'Hiburan', 'icon' => 'fas fa-gamepad', 'color' => '#EC4899'], 
            ['name' => 'Kesehatan', 'icon' => 'fas fa-heartbeat', 'color' => '#10B981'], 
            ['name' => 'Pendidikan', 'icon' => 'fas fa-graduation-cap', 'color' => '#6366F1'], 
            ['name' => 'Investasi', 'icon' => 'fas fa-chart-line', 'color' => '#14B8A6'], 
            ['name' => 'Hadiah & Donasi', 'icon' => 'fas fa-gift', 'color' => '#F97316'], 
            ['name' => 'Lainnya', 'icon' => 'fas fa-ellipsis-h', 'color' => '#6B7280'], 
        ];

        // Default Income Categories
        $incomeCategories = [
            ['name' => 'Gaji', 'icon' => 'fas fa-wallet', 'color' => '#10B981'],
            ['name' => 'Bonus', 'icon' => 'fas fa-award', 'color' => '#F59E0B'],
            ['name' => 'Investasi (Return)', 'icon' => 'fas fa-chart-pie', 'color' => '#3B82F6'],
            ['name' => 'Freelance', 'icon' => 'fas fa-laptop-code', 'color' => '#8B5CF6'],
            ['name' => 'Hadiah', 'icon' => 'fas fa-gifts', 'color' => '#EC4899'],
            ['name' => 'Penjualan', 'icon' => 'fas fa-store', 'color' => '#6366F1'],
            ['name' => 'Lainnya', 'icon' => 'fas fa-ellipsis-h', 'color' => '#6B7280'],
        ];

        // Seed Expenses
        foreach ($expenseCategories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name'], 'type' => 'expense'],
                [
                    'user_id' => null,
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'is_system' => true,
                ]
            );
        }

        // Seed Income
        foreach ($incomeCategories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name'], 'type' => 'income'],
                [
                    'user_id' => null,
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'is_system' => true,
                ]
            );
        }

        $this->command->info('Berhasil membuat 10 kategori pengeluaran default sistem.');
    }
}