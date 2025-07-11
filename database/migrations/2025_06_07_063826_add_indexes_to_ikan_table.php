<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ikan', function (Blueprint $table) {
            $table->index('nama_ikan');
            $table->index('harga');
            $table->index('stok');
            $table->index('status_ketersediaan');
            // Tidak perlu index untuk kategori_id jika sudah ada di foreignId
            // Tidak perlu index untuk slug jika sudah unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ikan', function (Blueprint $table) {
            $table->dropIndex(['nama_ikan']);
            $table->dropIndex(['harga']);
            $table->dropIndex(['stok']);
            $table->dropIndex(['status_ketersediaan']);
        });
    }
};
