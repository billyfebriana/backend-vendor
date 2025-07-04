<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor;
use App\Models\User;
use App\Models\VendorDocument;

class VendorProfileController extends Controller
{
    /**
     * Menampilkan data profil vendor untuk user yang sedang login.
     * (Tidak ada perubahan di method ini, diasumsikan accessor 'file_url' di model akan diupdate)
     */
    public function show(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }

            $vendor = Vendor::with('documents')->where('user_id', $user->id)->first();
            $vendorData = [];
            $documents = collect();

            if ($vendor) {
                $vendorData = $vendor->toArray();
                unset($vendorData['documents']);

                $documents = $vendor->documents->mapWithKeys(function ($doc) {
                    $frontendKey = str_replace([' ', '/'], ['', '_'], $doc->document_type);
                    return [
                        $frontendKey => [
                            'file_path' => $doc->file_url,
                            'status' => $doc->status,
                            'note' => $doc->note ?? null,
                        ]
                    ];
                });
            } else {
                $vendorData = ['id' => null, 'user_id' => $user->id];
            }

            return response()->json([
                'user_email' => $user->email,
                'vendor_profile' => $vendorData,
                'documents' => $documents,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor profile: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil profil', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Memperbarui atau Membuat data profil vendor (tanpa file).
     * (Tidak ada perubahan di method ini)
     */
    public function save(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }

            $validatedData = $request->validate([
                'company_name' => 'required|string|max:255',
                'domicile_address' => 'required|string',
                'npwp' => ['required', 'string', 'max:20', Rule::unique('vendors')->ignore($request->input('id'), 'id')],
                'npwp_address' => 'required|string',
                'pic_name' => 'required|string|max:255',
                'pic_position' => 'nullable|string|max:255',
                'pic_email' => 'nullable|email|max:255',
                'pic_phone' => 'nullable|string|max:50',
                'finance_name' => 'required|string|max:255',
                'finance_position' => 'nullable|string|max:255',
                'finance_email' => 'nullable|email|max:255',
                'finance_phone' => 'nullable|string|max:50',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:100',
                'account_holder_name' => 'nullable|string|max:255',
            ]);

            $vendor = Vendor::updateOrCreate(
                ['user_id' => $user->id],
                $validatedData
            );

            if ($user->vendor_id !== $vendor->id) {
                $user->vendor_id = $vendor->id;
                $user->save();
            }

            return response()->json(['message' => 'Profil vendor berhasil disimpan', 'vendor' => $vendor], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Kesalahan Validasi', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error saving vendor profile: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan profil', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengunggah dokumen vendor ke CEPH S3.
     * Endpoint: POST /api/vendor/documents/{documentType}
     */
    public function uploadDocument(Request $request, string $documentType)
    {
        // dd($request->all());
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return response()->json(['message' => 'Profil vendor tidak ditemukan. Harap lengkapi profil Anda terlebih dahulu.'], 404);
            }

            $dbDocumentTypeMap = [
                'AktaPerusahaan' => 'Akta Perusahaan', 'SPPKP' => 'SPPKP', 'SKT' => 'SKT',
                'NPWP' => 'NPWP', 'SIP_NIB' => 'SIP/NIB', 'NomorRekening' => 'Nomor Rekening',
            ];
            // dd(array_key_exists($documentType, $dbDocumentTypeMap));
            if (!array_key_exists($documentType, $dbDocumentTypeMap)) {
                return response()->json(['message' => 'Tipe dokumen tidak valid.'], 400);
            }
            $dbDocumentType = $dbDocumentTypeMap[$documentType];
            // dd($dbDocumentType);
            $request->validate(['document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);
            $file = $request->file('document');
            // --- PERUBAHAN --- Membuat folder khusus untuk dokumen vendor
            $folder = 'vendor_documents/vendor_' . $vendor->id;
            $uniqueName = $documentType . '_' . time() . '.' . $file->extension();

            // --- PERUBAHAN --- Hapus dokumen lama dari Ceph S3 jika ada
            $oldDocument = VendorDocument::where('vendor_id', $vendor->id)->where('document_type', $dbDocumentType)->first();
            if ($oldDocument && $oldDocument->file_path) {
                Storage::disk('s3')->delete($oldDocument->file_path);
            }

            // --- PERUBAHAN --- Simpan file baru ke Ceph S3 dan dapatkan path lengkapnya
            $storedPath = Storage::disk('s3')->putFileAs($folder, $file, $uniqueName);
            if (!$storedPath) {
                throw new \Exception('Gagal upload file ke Ceph S3.');
            }

            // --- PERUBAHAN --- Buat atau update record di DB menggunakan path S3
            $vendorDocument = VendorDocument::updateOrCreate(
                ['vendor_id' => $vendor->id, 'document_type' => $dbDocumentType],
                [
                    'file_path'         => $storedPath, // Menyimpan path lengkap
                    'filename'          => $uniqueName,
                    'status'            => 'Pending',
                    'note'              => null,
                ]
            );

            // --- PERUBAHAN --- Gunakan Storage facade untuk membuat URL publik yang benar
            $publicUrl = Storage::disk('s3')->url($storedPath);

            return response()->json([
                'message' => 'Dokumen berhasil diunggah',
                'document' => [
                    'document_type' => $vendorDocument->document_type,
                    'file_path' => $publicUrl, // Kirim URL lengkap ke frontend
                    'status' => $vendorDocument->status,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Kesalahan Validasi', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading document: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Terjadi kesalahan saat mengunggah dokumen', 'error' => $e->getMessage()], 500);
        }
    }
}
