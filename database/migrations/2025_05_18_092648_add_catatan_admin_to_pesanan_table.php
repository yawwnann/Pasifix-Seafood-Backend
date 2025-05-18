<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_catatan_admin_to_pesanan_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->text('catatan_admin')->nullable()->after('catatan'); // Atau sesuaikan posisi
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('catatan_admin');
        });
    }
};