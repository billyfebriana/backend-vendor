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
        // Membuat tabel 'goods_receipt_notes' (GRN) untuk menyimpan Good Received Notes.
        // Data ini kemungkinan diimpor atau disinkronkan dari sistem ERP.
        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('vendor_id'); // GRN ini terkait dengan vendor mana
            $table->string('supplier_name')->nullable(); // Nama supplier (perusahaan yang mengeluarkan GRN ke vendor kita)
            $table->string('grn_number', 100)->unique(); // Nomor GRN
            $table->date('grn_date'); // Tanggal GRN
            $table->string('po_number', 100); // Nomor PO yang terkait dengan GRN
            $table->enum('status', ['Accepted', 'Pending'])->default('Pending');
            $table->string('document_path')->nullable(); // Path untuk mendownload GRN
            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'vendors'.
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            // Menambahkan index untuk kolom po_number untuk pencarian yang lebih cepat.
            $table->index('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'goods_receipt_notes' jika rollback dilakukan.
        Schema::dropIfExists('goods_receipt_notes');
    }
};

