<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VendorDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id', 'document_type', 'file_path', 'status', 'note'
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}