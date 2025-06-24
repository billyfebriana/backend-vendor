<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\VendorFile;
use Illuminate\Support\Facades\DB; // Impor DB untuk transaksi
use Illuminate\Support\Facades\Log; // Impor Log untuk mencatat error

class UploaderController extends Controller
{
    public function upload(Request $request, $documentType)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,png|max:5120',
        ]);

        // Nanti, dapatkan ID ini secara dinamis, misalnya dari user yang login
        $vendorId = 1; 
        // $vendorId = auth()->id(); // Contoh dinamis

        $path = null; // Inisialisasi path di luar try-catch

        try {
            DB::beginTransaction(); // Mulai transaksi

            $file = $request->file('file');
            $uniqueName = $documentType . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();

            // 1. Upload ke S3
            $path = $file->storeAs("vendor/{$vendorId}", $uniqueName, 's3');
            Storage::disk('s3')->setVisibility($path, 'public');

            // Panggil url() sekali saja
            $s3Url = Storage::disk('s3')->url($path);

            // 2. Simpan ke Database
            VendorFile::updateOrCreate(
                ['vendor_id' => $vendorId, 'document_type' => $documentType],
                ['filename' => $uniqueName, 's3_path' => $s3Url]
            );

            DB::commit(); // Konfirmasi transaksi jika semua berhasil

            return response()->json([
                'message' => 'Upload berhasil!',
                'file_name' => $uniqueName,
                's3_url' => $s3Url,
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua operasi database

            // Jika file sudah terupload ke S3 tapi DB gagal, hapus lagi filenya
            if ($path) {
                Storage::disk('s3')->delete($path);
            }

            // Catat error untuk developer
            Log::error("Upload Gagal: " . $e->getMessage());

            // Kirim respons error yang ramah ke pengguna
            return response()->json([
                'message' => 'Terjadi kesalahan saat meng-upload file.'
            ], 500);
        }
    }
}