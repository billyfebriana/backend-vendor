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
        // Membuat tabel 'invoice_documents' untuk file lampiran faktur (DO, Invoice, Faktur, Lampiran lain).
        Schema::create('invoice_documents', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('invoice_id'); // Foreign Key ke invoices
            $table->enum('document_type', ['Delivery Order', 'Invoice', 'Faktur Pajak', 'Lampiran']);
            $table->string('file_path'); // Path ke file PDF
            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'invoices'.
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            // Memastikan hanya satu jenis dokumen per faktur.
            $table->unique(['invoice_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'invoice_documents' jika rollback dilakukan.
        Schema::dropIfExists('invoice_documents');
    }
};

