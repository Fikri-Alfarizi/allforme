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
        Schema::create('digital_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform_name');
            $table->enum('type', ['income_source', 'wallet'])->default('income_source'); // e.g., 'income_source' for ShrinkMe, 'wallet' for PayPal
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('currency')->default('IDR');
            $table->string('website_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_accounts');
    }
};
