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
        // Membuat tabel 'taxes' untuk detail pajak terkait faktur.
        Schema::create('taxes', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('invoice_id'); // Foreign Key ke invoices
            $table->decimal('dpp_value', 15, 2)->nullable(); // Nilai DPP
            $table->decimal('ppn_value', 15, 2)->nullable(); // Nilai PPN
            $table->decimal('total_dpp_ppn', 15, 2)->nullable(); // DPP + PPN
            $table->string('wht_code', 50)->nullable(); // Kode PPh (contoh: PPH 23, PPH 4(2))
            $table->decimal('base_amount', 15, 2)->nullable(); // Dasar perhitungan WHT
            $table->decimal('wht_amount', 15, 2)->nullable(); // Jumlah Withholding Tax
            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'invoices'.
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'taxes' jika rollback dilakukan.
        Schema::dropIfExists('taxes');
    }
};

