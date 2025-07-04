<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // --- PERUBAHAN 1: Menambahkan import Storage ---

class VendorDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // --- PERUBAHAN 2: Memperbarui kolom yang bisa diisi ---
    protected $fillable = [
        'vendor_id',
        'document_type',
        'file_path', // Menggantikan seaweedfs_file_id
        'filename',  // Menambahkan filename untuk referensi
        'status',
        'note'
    ];

    /**
     * Relasi ke model Vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // --- PERUBAHAN 3: Menambahkan Accessor untuk 'file_url' ---
    /**
     * Accessor untuk mendapatkan URL publik file dari S3 (Ceph) secara dinamis.
     * Akan dipanggil setiap kali kita mengakses properti $document->file_url
     *
     * @return string|null
     */
    public function getFileUrlAttribute(): ?string
    {
        // Cek apakah ada path file yang tersimpan di database
        if ($this->file_path) {
            // Gunakan Storage facade untuk membuat URL yang benar dari disk 's3'.
            // Ini akan otomatis menggunakan konfigurasi endpoint dari .env
            return Storage::disk('s3')->url($this->file_path);
        }

        // Kembalikan null jika tidak ada file
        return null;
    }
}
