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
        // Membuat tabel 'users' untuk menyimpan data user (Admin dan User Vendor).
        // User Vendor tidak register mandiri, akun dibuatkan oleh Admin.
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('email')->unique(); // email VARCHAR(255) UNIQUE NOT NULL
            $table->string('password'); // password VARCHAR(255) NOT NULL (akan di-hash)
            $table->enum('role', ['admin', 'vendor'])->default('vendor'); // role ENUM('admin', 'vendor') NOT NULL
            $table->unsignedBigInteger('vendor_id')->nullable(); // vendor_id BIGINT UNSIGNED NULL

            // Catatan: Foreign key ke tabel 'vendors' akan ditambahkan di migration terpisah
            // untuk memastikan tabel 'vendors' sudah ada terlebih dahulu.

            $table->rememberToken(); // remember_token VARCHAR(100) NULL
            $table->timestamps(); // created_at dan updated_at TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus tabel 'users' jika rollback dilakukan.
        Schema::dropIfExists('users');
    }
};