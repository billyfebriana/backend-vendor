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
        // Menambahkan foreign key 'vendor_id' ke tabel 'users'.
        // Ini dilakukan di migration terpisah untuk memastikan tabel 'vendors' sudah ada.
        Schema::table('users', function (Blueprint $table) {
            // !!! PENTING: Baris ini dihapus dari up() karena sering menyebabkan error
            // $table->dropForeign(['vendor_id']); 

            // Tambahkan foreign key constraint
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus foreign key 'vendor_id' dari tabel 'users' jika rollback dilakukan.
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });
    }
};

