<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\VendorFile; // Pastikan namespace model ini benar
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploaderController extends Controller
{
    public function upload(Request $request, $documentType)
    {
        $request->validate([
            // Sesuaikan validasi jika perlu
            'file' => 'required|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        // Nanti, dapatkan ID ini secara dinamis
        $vendorId = 1;
        // $vendorId = auth()->id();

        // Inisialisasi variabel untuk error handling
        $seaweedFsId = null;

        try {
            DB::beginTransaction(); // Mulai transaksi

            $file = $request->file('file');
            // Nama file bisa tetap pakai pendekatanmu atau disederhanakan
            $uniqueName = 'vendor' . $vendorId . '_' . $documentType . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();

            // 1. Upload ke SeaweedFS
            // Kita ganti disk 's3' menjadi 'seaweedfs'
            // Method 'put' akan mengembalikan ID file dari SeaweedFS
            $seaweedFsId = Storage::disk('seaweedfs')->put(
                $uniqueName,
                file_get_contents($file->getRealPath())
            );

            // Jika gagal upload, seaweedFsId akan null/false
            if (!$seaweedFsId) {
                // Lemparkan exception agar masuk ke blok catch
                throw new \Exception('File upload to SeaweedFS failed.');
            }

            // 2. Simpan ke Database
            // Ganti 's3_path' dengan 'seaweedfs_file_id'
            VendorFile::updateOrCreate(
                ['vendor_id' => $vendorId, 'document_type' => $documentType],
                [
                    'filename' => $uniqueName,
                    'seaweedfs_file_id' => $seaweedFsId // <-- Simpan ID SeaweedFS
                ]
            );

            DB::commit(); // Konfirmasi transaksi

            // Buat URL lengkap untuk respons JSON
            $fileUrl = rtrim(env('SEAWEEDFS_PUBLIC_URL'), '/') . '/' . $seaweedFsId;

            return response()->json([
                'message' => 'Upload berhasil!',
                'file_name' => $uniqueName,
                'file_url' => $fileUrl, // Ganti dari s3_url
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan operasi database

            // Jika file sudah terupload ke SeaweedFS tapi DB gagal, hapus lagi filenya
            if ($seaweedFsId) {
                // Gunakan ID yang didapat untuk menghapus
                Storage::disk('seaweedfs')->delete($seaweedFsId);
            }

            // Catat error untuk developer
            Log::error("Upload Gagal: " . $e->getMessage() . " on line " . $e->getLine());

            // Kirim respons error ke pengguna
            return response()->json([
                'message' => 'Terjadi kesalahan saat meng-upload file.'
            ], 500);
        }
    }
}
