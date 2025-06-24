<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login
use Illuminate\Support\Facades\Storage; // Untuk menyimpan file
use Illuminate\Validation\Rule; // Untuk validasi unique
use App\Models\Vendor; // Import model Vendor
use App\Models\User; // Import model User
use App\Models\VendorDocument; // Import model VendorDocument

class VendorProfileController extends Controller
{
    /**
     * Tampilkan data profil vendor untuk user yang sedang login.
     * Jika tidak ada profil, kembalikan struktur data kosong.
     * Endpoint: GET /api/vendor/profile
     */
    public function show(Request $request)
    {
        try {
            $user = Auth::user();

            // Pastikan user memiliki role 'vendor'
            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }

            // Coba temukan profil vendor berdasarkan user_id
            $vendor = Vendor::with('documents')->where('user_id', $user->id)->first();

            $vendorData = [];
            $documents = collect(); // Inisialisasi koleksi kosong untuk dokumen

            if ($vendor) {
                // Jika profil vendor ditemukan, format datanya
                $vendorData = [
                    'id' => $vendor->id,
                    'company_name' => $vendor->company_name,
                    'domicile_address' => $vendor->domicile_address,
                    'npwp' => $vendor->npwp,
                    'npwp_address' => $vendor->npwp_address,
                    'pic_name' => $vendor->pic_name,
                    'pic_position' => $vendor->pic_position,
                    'pic_email' => $vendor->pic_email,
                    'pic_phone' => $vendor->pic_phone,
                    'finance_name' => $vendor->finance_name,
                    'finance_position' => $vendor->finance_position,
                    'finance_email' => $vendor->finance_email,
                    'finance_phone' => $vendor->finance_phone,
                    'bank_name' => $vendor->bank_name,
                    'account_number' => $vendor->account_number,
                    'account_holder_name' => $vendor->account_holder_name,
                ];

                // Format data dokumen untuk frontend (menggunakan key frontend)
                // Pastikan key-nya cocok dengan documentTypeMap di frontend
                $documents = $vendor->documents->mapWithKeys(function ($doc) {
                    $frontendKey = str_replace([' ', '/'], ['','_'], $doc->document_type); // Konversi Akta Perusahaan -> AktaPerusahaan, SIP/NIB -> SIP_NIB
                    return [
                        $frontendKey => [
                            'file_path' => Storage::url($doc->file_path),
                            'status' => $doc->status,
                            'note' => $doc->note ?? null,
                        ]
                    ];
                });
            } else {
                // Jika profil vendor belum ada, kembalikan struktur kosong dengan user_id
                $vendorData = [
                    'id' => null, // Tandai sebagai profil baru di frontend
                    'user_id' => $user->id, // Sertakan user_id untuk pembuatan pertama kali
                    'company_name' => '', 'domicile_address' => '', 'npwp' => '', 'npwp_address' => '',
                    'pic_name' => '', 'pic_position' => '', 'pic_email' => '', 'pic_phone' => '',
                    'finance_name' => '', 'finance_position' => '', 'finance_email' => '', 'finance_phone' => '',
                    'bank_name' => '', 'account_number' => '', 'account_holder_name' => '',
                ];
            }

            // Gabungkan data user email, vendor profile, dan dokumen ke respons
            return response()->json([
                'user_email' => $user->email,
                'vendor_profile' => $vendorData,
                'documents' => $documents,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching vendor profile: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil profil', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Perbarui atau Buat data profil vendor (upsert).
     * Endpoint: POST /api/vendor/profile/save
     */
    public function save(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }

            // Validasi data yang masuk
            $validatedData = $request->validate([
                'company_name' => 'required|string|max:255',
                'domicile_address' => 'required|string',
                // NPWP perlu unik, tapi boleh sama jika itu update untuk vendor_id yang sama
                'npwp' => [
                    'required', 'string', 'max:20',
                    Rule::unique('vendors')->ignore($request->input('id'), 'id'), // Mengabaikan ID yang dikirim dari frontend jika ada
                ],
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

            // Temukan atau buat profil vendor
            $vendor = Vendor::updateOrCreate(
                ['user_id' => $user->id], // Kriteria untuk menemukan atau membuat
                $validatedData // Data yang akan diisi/update
            );

            // Penting: Update vendor_id di tabel users jika baru dibuat
            if ($user->vendor_id !== $vendor->id) {
                $user->vendor_id = $vendor->id;
                $user->save();
            }

            return response()->json([
                'message' => 'Profil vendor berhasil disimpan',
                'vendor' => $vendor
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan Validasi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error saving vendor profile: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan profil', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unggah dokumen vendor.
     * Endpoint: POST /api/vendor/documents/{documentType}
     * documentType akan berupa format URL-friendly (e.g., AktaPerusahaan, SIP_NIB)
     */
    public function uploadDocument(Request $request, string $documentType)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'vendor') {
                return response()->json(['message' => 'Tidak sah atau Bukan pengguna vendor'], 403);
            }

            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return response()->json(['message' => 'Profil vendor tidak ditemukan. Harap lengkapi profil Anda terlebih dahulu.'], 404);
            }

            // Gunakan format KEY dari frontend documentTypeMap
            $validDocumentKeys = [
                'AktaPerusahaan', 'SPPKP', 'SKT', 'NPWP', 'SIP_NIB', 'NomorRekening'
            ];
            // Mapping untuk menyimpan ke database ENUM (dengan spasi jika diperlukan)
            $dbDocumentTypeMap = [
                'AktaPerusahaan' => 'Akta Perusahaan',
                'SPPKP' => 'SPPKP',
                'SKT' => 'SKT',
                'NPWP' => 'NPWP',
                'SIP_NIB' => 'SIP/NIB',
                'NomorRekening' => 'Nomor Rekening',
            ];

            if (!in_array($documentType, $validDocumentKeys)) {
                return response()->json(['message' => 'Tipe dokumen tidak valid.'], 400);
            }

            // Ambil nilai yang sesuai untuk disimpan ke database ENUM
            $dbDocumentType = $dbDocumentTypeMap[$documentType];


            $request->validate([
                'document' => 'required|file|mimes:pdf|max:5120', // Hanya PDF, max 5MB
            ]);

            // Hapus dokumen lama jika ada
            $oldDocument = VendorDocument::where('vendor_id', $vendor->id)
                                         ->where('document_type', $dbDocumentType)
                                         ->first();
            if ($oldDocument && Storage::exists($oldDocument->file_path)) {
                Storage::delete($oldDocument->file_path);
            }

            // Simpan file baru ke storage
            // Path akan menggunakan format URL-friendly key
            $filePath = $request->file('document')->store(
                'vendor_documents/' . $vendor->id . '/' . $documentType, // Menggunakan $documentType (key frontend) untuk nama folder
                'public' // Gunakan disk 'public' agar bisa diakses via URL
            );

            // Buat atau update record di database vendor_documents
            $vendorDocument = VendorDocument::updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'document_type' => $dbDocumentType, // Simpan nilai ENUM yang benar ke DB
                ],
                [
                    'file_path' => $filePath,
                    'status' => 'Pending', // Status awal setelah upload adalah Pending, menunggu review admin
                    'note' => null, // Reset note
                ]
            );

            return response()->json([
                'message' => 'Dokumen berhasil diunggah',
                'document' => [
                    'document_type' => $vendorDocument->document_type,
                    'file_path' => Storage::url($vendorDocument->file_path),
                    'status' => $vendorDocument->status,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Kesalahan Validasi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading document: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengunggah dokumen', 'error' => $e->getMessage()], 500);
        }
    }
}

