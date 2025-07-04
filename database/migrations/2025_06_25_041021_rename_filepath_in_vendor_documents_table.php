<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_documents', function (Blueprint $table) {
            // Mengganti nama kolom 'file_path' menjadi 'seaweedfs_file_id'
            // Ini lebih deskriptif untuk menyimpan ID dari SeaweedFS.
            $table->renameColumn('file_path', 'seaweedfs_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_documents', function (Blueprint $table) {
            // Logika jika migrasi di-rollback
            $table->renameColumn('seaweedfs_file_id', 'file_path');
        });
    }
};
