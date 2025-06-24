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
        // Membuat tabel 'invoices' untuk menyimpan data faktur yang diajukan vendor.
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('vendor_id'); // Faktur ini diajukan oleh vendor mana
            $table->unsignedBigInteger('po_id')->nullable(); // Foreign Key ke purchase_orders (PO terkait)
            $table->string('po_number', 100); // Copy dari PO number untuk kemudahan query
            $table->date('upload_date'); // Tgl upload
            $table->string('invoice_number', 100)->unique(); // no invoice
            $table->date('invoice_date'); // tgl invoice
            $table->string('tax_invoice_number', 100)->nullable(); // no faktur pajak
            $table->date('tax_invoice_date')->nullable(); // tanggal faktur pajak
            $table->string('delivery_note_number', 100)->nullable(); // no surat jalan
            $table->date('delivery_note_date')->nullable(); // tanggal surat jalan
            $table->decimal('total_calculated_items_value', 15, 2)->nullable(); // Total nilai item terpilih (dihitung dari Invoice_Details)
            $table->decimal('additional_cost', 15, 2)->default(0.00); // Biaya tambahan
            $table->decimal('total_invoice_paid', 15, 2)->nullable(); // Total akhir yang dibayarkan (setelah pajak)
            $table->enum('status', ['Pending', 'Submitted', 'Approved', 'Rejected', 'Paid'])->default('Pending'); // Status faktur
            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'vendors'.
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            // Menambahkan foreign key ke tabel 'purchase_orders'.
            $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('set null');
            // Menambahkan index untuk kolom po_number untuk pencarian yang lebih cepat.
            $table->index('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'invoices' jika rollback dilakukan.
        Schema::dropIfExists('invoices');
    }
};

