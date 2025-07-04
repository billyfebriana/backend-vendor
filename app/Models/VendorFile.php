<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vendor_id',
        'document_type',
        'seaweedfs_file_id', // <-- PASTIKAN INI ADA
        'filename',          // <-- TAMBAHKAN INI (OPSIONAL TAPI BAGUS)
        'status',
        'note',
    ];

    /**
     * Accessor untuk mendapatkan URL publik file dari SeaweedFS.
     * Kita akan panggil ini dengan: $document->file_url
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => rtrim(env('SEAWEEDFS_PUBLIC_URL'), '/') . '/' . $this->seaweedfs_file_id
        );
    }
}
