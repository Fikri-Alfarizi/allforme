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
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('prompt');
            $table->text('response');
            $table->json('context_data')->nullable()->comment('Financial data summary sent to AI');
            $table->integer('tokens_used')->nullable();
            $table->integer('response_time')->nullable()->comment('Response time in milliseconds');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
