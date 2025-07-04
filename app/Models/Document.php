<?php

namespace App\Models;

// 1. IMPORT HasFactory DITAMBAHKAN DI SINI
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Baris 'use ;' yang error sudah saya hapus

class Document extends Model
{
    // Trait ini sekarang sudah dikenali karena sudah di-import di atas
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // 2. $fillable DIISI DENGAN KOLOM DARI MIGRATION ANDA
    protected $fillable = [
        'original_name',
        'path',
    ];
}
