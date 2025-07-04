<?php

namespace App\Http\Controllers;

// dalam file app/Http/Controllers/DocumentController.php

use App\Models\Document; // <-- Jangan lupa import model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- Jangan lupa import Storage

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        // 1. Validasi request: pastikan ada file, tipenya pdf, dan ukurannya tidak lebih dari 10MB
        $request->validate([
            'document' => 'required|file|mimes:pdf, jpeg, png |max:10240', // max 10MB
        ]);

        // 2. Ambil file dari request
        $file = $request->file('document');

        // 3. Dapatkan nama asli file untuk disimpan di database
        $originalName = $file->getClientOriginalName();

        // 4. Buat nama unik untuk file agar tidak ada nama yang sama di bucket
        $uniqueName = time() . '_' . $originalName;

        // 5. Upload file ke Ceph (disk 's3') di dalam folder 'documents'
        // Perintah ini akan mengembalikan path lengkap dari file yang disimpan
        try{
            $storedPath = Storage::disk('s3')->putFileAs('documents', $file, $uniqueName);

            if (!$storedPath) {// 7. Berikan respon sukses dalam format JSON
                return response()->json([
                    'message' => 'File tidak berhasil di-upload.',
                ], 400); // 201 artinya 'Created'

            }else{

                // 6. Simpan informasi file ke database MySQL
                $document = Document::create([
                    'original_name' => $originalName,
                    'path' => $storedPath,
                ]);
                // 7. Berikan respon sukses dalam format JSON
                return response()->json([
                    'message' => 'File PDF berhasil di-upload.',
                    'data' => $document
                ], 201); // 201 artinya 'Created'

            }
        }catch (\Exception $e){
            dd($e);
        }


    }
    public function download($id)
    {
        // 1. Cari record dokumen di database berdasarkan ID-nya
        $document = Document::findOrFail($id);

        // 2. Gunakan path yang tersimpan di database untuk mengambil file dari Ceph
        //    dan langsung menyajikannya sebagai download dengan nama aslinya.
        return Storage::disk('s3')->download($document->path, $document->original_name);
    }
}
