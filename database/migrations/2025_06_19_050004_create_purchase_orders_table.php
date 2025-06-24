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
        // Membuat tabel 'purchase_orders' untuk menyimpan history Purchase Orders (PO).
        // Data ini kemungkinan diimpor atau disinkronkan dari sistem ERP.
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('vendor_id'); // PO ini terkait dengan vendor mana
            $table->string('supplier_name')->nullable(); // Nama supplier (perusahaan yang mengeluarkan PO ke vendor kita)
            $table->string('po_number', 100)->unique(); // Nomor PO (diasumsikan unik secara global)
            $table->date('po_date'); // Tanggal PO
            $table->date('exp_date')->nullable(); // Tanggal kedaluwarsa PO
            $table->enum('status', ['Aktif', 'Non Aktif'])->default('Aktif');
            $table->string('document_path')->nullable(); // Path untuk mendownload PO
            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'vendors'.
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'purchase_orders' jika rollback dilakukan.
        Schema::dropIfExists('purchase_orders');
    }
};

