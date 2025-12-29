<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Pastikan UserSeeder dijalankan pertama jika kamu memilikinya
            // UserSeeder::class, 
            ExpenseCategorySeeder::class,
            DigitalAccountSeeder::class,
        ]);
    }
}