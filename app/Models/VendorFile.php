<?php


// app/Models/VendorFile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorFile extends Model
{
    protected $fillable = [
        'vendor_id',
        'document_type',
        'filename',
        's3_path',
    ];
}
