<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@plfis.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create default settings for demo user
        Setting::create([
            'user_id' => $user->id,
            'currency' => 'IDR',
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'theme' => 'dark',
            'notification_enabled' => true,
            'ai_enabled' => true,
            'vault_timeout_minutes' => 5,
        ]);

        $this->command->info('Demo user created: demo@plfis.com / password');
    }
}
