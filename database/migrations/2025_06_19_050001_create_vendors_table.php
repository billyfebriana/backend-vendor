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
        // Membuat tabel 'vendors' untuk menyimpan informasi lengkap perusahaan vendor.
        // Berhubungan satu-satu dengan tabel 'users' (satu user bisa kelola satu vendor).
        Schema::create('vendors', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('user_id')->unique(); // Foreign Key ke tabel users (satu user mengelola satu vendor)
            $table->string('company_name'); // nama_perusahaan VARCHAR(255) NOT NULL
            $table->text('domicile_address'); // Alamat_domisili TEXT NOT NULL
            $table->string('npwp', 20)->unique(); // NPWP VARCHAR(20) UNIQUE NOT NULL
            $table->text('npwp_address'); // alamat_sesuai_npwp TEXT NOT NULL
            $table->string('pic_name'); // Nama_PIC VARCHAR(255) NOT NULL
            $table->string('pic_position')->nullable(); // Jabatan_PIC VARCHAR(255) NULL
            $table->string('pic_email')->nullable(); // Email_PIC VARCHAR(255) NULL
            $table->string('pic_phone', 50)->nullable(); // Telepon_PIC VARCHAR(50) NULL
            $table->string('finance_name'); // Nama_finance VARCHAR(255) NOT NULL
            $table->string('finance_position')->nullable(); // Jabatan_Finance VARCHAR(255) NULL
            $table->string('finance_email')->nullable(); // Email_Finance VARCHAR(255) NULL
            $table->string('finance_phone', 50)->nullable(); // Telepon_Finance VARCHAR(50) NULL
            $table->string('bank_name')->nullable(); // Nama_Bank VARCHAR(255) NULL
            $table->string('account_number', 100)->nullable(); // No_Rekening VARCHAR(100) NULL
            $table->string('account_holder_name')->nullable(); // Nama_Rekening VARCHAR(255) NULL

            // --- TAMBAHAN BARU: KOLOM 'vendor_type' ---
            $table->enum('vendor_type', ['vendorExpedisi', 'vendorTrade', 'vendorNonTrade', 'vendorDistributor', 'vendorClaimInternal'])->nullable();

            $table->timestamps(); // created_at dan updated_at TIMESTAMP

            // Menambahkan foreign key ke tabel 'users'.
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'vendors' jika rollback dilakukan.
        Schema::dropIfExists('vendors');
    }
};

