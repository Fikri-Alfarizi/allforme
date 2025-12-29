<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts_vault', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('account_type', ['email', 'game', 'social_media', 'website', 'api', 'other']);
            $table->string('service_name');
            $table->text('username'); // Encrypted
            $table->text('email')->nullable(); // Encrypted
            $table->text('password'); // Encrypted
            $table->text('notes')->nullable(); // Encrypted
            $table->string('url')->nullable();
            $table->date('last_password_change')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'account_type']);
            $table->index(['user_id', 'service_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_vault');
    }
};
