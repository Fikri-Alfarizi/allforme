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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('source')->nullable()->comment('Income source or expense merchant');
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'transaction_date']);
            $table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
