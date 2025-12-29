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
        Schema::table('digital_accounts', function (Blueprint $table) {
            // 1. Hapus constraint foreign key sementara
            $table->dropForeign(['user_id']);

            // 2. Ubah kolom user_id agar boleh null (nullable)
            $table->foreignId('user_id')->nullable()->change();

            // 3. Tambahkan kembali constraint foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // 4. Tambahkan kolom is_system untuk menandai akun default sistem
            $table->boolean('is_system')->default(false)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_accounts', function (Blueprint $table) {
            // Hapus kolom is_system
            $table->dropColumn('is_system');

            // Kembalikan user_id seperti semula (tidak boleh null)
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};