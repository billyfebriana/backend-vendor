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
        // Membuat tabel 'vendor_documents' untuk menyimpan file PDF perusahaan vendor.
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('vendor_id'); // Foreign Key ke tabel vendors
            $table->enum('document_type', ['Akta Perusahaan', 'SPPKP', 'SKT', 'NPWP', 'SIP/NIB', 'Nomor Rekening']);
            $table->string('file_path'); // Path ke file PDF yang di-upload
            $table->enum('status', ['Approved', 'Revisi', 'Ditolak', 'Pending'])->default('Pending'); // Status dokumen
            $table->timestamps(); // created_at dan updated_at TIMESTAMP
            $table->string('note');

            // Menambahkan foreign key ke tabel 'vendors'.
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            // Memastikan hanya satu jenis dokumen per vendor.
            $table->unique(['vendor_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'vendor_documents' jika rollback dilakukan.
        Schema::dropIfExists('vendor_documents');
    }
};

