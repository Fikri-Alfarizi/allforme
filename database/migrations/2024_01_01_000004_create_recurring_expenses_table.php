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
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->date('next_due_date');
            $table->boolean('is_active')->default(true);
            $table->integer('reminder_days_before')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active', 'next_due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
