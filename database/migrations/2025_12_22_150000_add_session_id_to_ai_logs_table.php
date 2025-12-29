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
        Schema::table('ai_logs', function (Blueprint $table) {
            $table->string('session_id')->nullable()->index()->after('user_id');
            // Optional: Add title for the session
             $table->string('title')->nullable()->after('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_logs', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'title']);
        });
    }
};
