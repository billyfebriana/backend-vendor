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
        // Membuat tabel 'invoice_details' untuk detail item di dalam faktur.
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('invoice_id'); // Foreign Key ke invoices
            $table->integer('line_number'); // line
            $table->string('material'); // material
            $table->decimal('qty_po', 15, 2); // qty PO
            $table->decimal('qty_do', 15, 2); // qty do
            $table->decimal('price', 15, 2); // Price
            $table->decimal('order_value', 15, 2); // order value (qty_do * price)
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
        // Menghapus tabel 'invoice_details' jika rollback dilakukan.
        Schema::dropIfExists('invoice_details');
    }
};

