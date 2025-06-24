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
        // Membuat tabel 'invoice_receipts' untuk tanda terima faktur.
        Schema::create('invoice_receipts', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('invoice_id'); // Foreign Key ke invoices
            $table->string('reff_number', 100)->unique(); // Nomor referensi tanda terima
            $table->date('due_date')->nullable(); // Tanggal jatuh tempo pembayaran
            $table->enum('status', ['Issued', 'Pending Payment', 'Paid'])->default('Issued'); // Status tanda terima
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
        // Menghapus tabel 'invoice_receipts' jika rollback dilakukan.
        Schema::dropIfExists('invoice_receipts');
    }
};

