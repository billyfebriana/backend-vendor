<?php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| ... (kode route Anda yang lain mungkin ada di sini) ...
*/

// RUTE UNTUK TEST UPLOAD KE CEPH RGW
Route::get('/test-s3-upload', function () {
    try {
    // 1. Tentukan nama file dan kontennya untuk tes
    $fileName = 'percobaan-upload-' . now()->timestamp . '.txt';
    $fileContents = 'Halo dari Laravel! File ini berhasil di-upload ke Ceph RGW pada ' . now();

    // 2. Gunakan Storage facade untuk meng-upload ke disk 's3'.
    //    Laravel akan otomatis menggunakan konfigurasi dari .env Anda.
    Storage::disk('s3')->put($fileName, $fileContents, 'public');

    // 3. Dapatkan URL file yang baru di-upload
    $url = Storage::disk('s3')->url($fileName);

    // 4. Beri pesan sukses beserta URL-nya
    return "<h2>Sukses!</h2>
    <p>File '{$fileName}' berhasil di-upload ke Ceph RGW!</p>
    <p>Anda bisa mengaksesnya di: <a href='{$url}' target='_blank'>{$url}</a></p>";

    } catch (\Exception $e) {
    // 5. Jika terjadi error, tampilkan pesannya agar mudah di-debug
    return "<h2>Gagal Meng-upload File!</h2>
    <p>Pesan Error: " . $e->getMessage() . "</p>";
    }
});
