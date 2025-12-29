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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('currency')->default('IDR');
            $table->string('language')->default('id');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->boolean('notification_enabled')->default(true);
            $table->boolean('ai_enabled')->default(true);
            $table->integer('vault_timeout_minutes')->default(15);
            $table->json('preferences')->nullable()->comment('Additional user preferences');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
