<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'domicile_address',
        'npwp',
        'npwp_address',
        'pic_name',
        'pic_position',
        'pic_email',
        'pic_phone',
        'finance_name',
        'finance_position',
        'finance_email',
        'finance_phone',
        'bank_name',
        'account_number',
        'account_holder_name',
        'vendor_type', // <<< INI YANG DITAMBAHKAN
    ];

    // Relasi ke VendorDocument
    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

