<?php

// File: s3_test.php

use Illuminate\Support\Facades\Storage;

echo "Mencoba meng-upload file tes ke S3...\n";

try {
    // Konten dan nama file untuk tes
    $content = "Ini adalah file tes yang di-upload dari Tinker pada " . now();
    $filename = "tinker-test/test-" . time() . ".txt";

    // Mencoba upload dengan visibility public
    $result = Storage::disk('s3')->put($filename, $content, 'public');

    if ($result) {
        echo "✅ SUKSES! File berhasil di-upload.\n";
        echo "   Path di S3: " . $filename . "\n";
        echo "   Silakan cek bucket S3 Anda untuk memverifikasi.\n";
    } else {
        echo "❌ GAGAL! Operasi upload mengembalikan 'false'. Ini biasanya berarti ada masalah izin atau konfigurasi.\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR TERJADI SAAT UPLOAD:\n";
    echo "================================\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    echo "================================\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Baris: " . $e->getLine() . "\n";
}