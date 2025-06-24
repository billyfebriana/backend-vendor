<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // PERBAIKAN 1: Menambahkan import class Rule

class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar semua pengguna.
     * Endpoint: GET /api/admin/users
     */
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Anda bukan admin.'], 403);
        }

        // PERBAIKAN 3: Menggunakan select eksplisit untuk menghindari timpa kolom 'id'
        $users = User::leftJoin('vendors as v', 'v.user_id', '=', 'users.id')
            ->select(
                'users.id', 
                'users.email', 
                'users.role', 
                'v.id as vendor_id', 
                'v.company_name', 
                'v.pic_name', 
                'v.pic_phone'
            )
            ->get();

        // Mapping tidak perlu diubah, karena query sudah diperbaiki
        return response()->json([
            'message' => 'Daftar pengguna berhasil diambil',
            'users' => $users
        ], 200);
    }

    /**
     * Tampilkan detail profil vendor untuk validasi Admin.
     * Endpoint: GET /api/admin/vendors/{id}/validate
     */
    public function showVendorForValidation(string $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Anda bukan admin.'], 403);
        }

        $user = User::with('vendor.documents')->find($id);

        if (!$user || !$user->vendor) {
            return response()->json(['message' => 'Vendor tidak ditemukan.'], 404);
        }

        $vendor = $user->vendor;

        $documents = $vendor->documents->mapWithKeys(function ($doc) {
            $frontendKey = str_replace([' ', '/'], ['', '_'], $doc->document_type);
            return [
                $frontendKey => [
                    'file_path' => $doc->file_path ? Storage::url($doc->file_path) : null,
                    'status' => $doc->status,
                    'note' => $doc->note,
                ]
            ];
        });
        
        // PERBAIKAN 4: Menyederhanakan kode, map tidak diperlukan
        return response()->json([
            'vendorData' => [
                'id' => $vendor->id,
                'user_id' => $user->id,
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
                'vendor_type' => $vendor->vendor_type, // Langsung ambil dari DB
                'documents' => $documents,
            ]
        ], 200);
    }

    /**
     * Update status dokumen vendor dan tipe vendor (oleh Admin).
     * Endpoint: POST /api/admin/vendors/{id}/validate
     */
    public function updateVendorValidation(Request $request, string $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Anda bukan admin.'], 403);
        }

        $user = User::find($id);
        if (!$user || !$user->vendor) {
            return response()->json(['message' => 'Vendor tidak ditemukan.'], 404);
        }

        $vendor = $user->vendor;

        // ... di dalam method updateVendorValidation
        $request->validate([
            'documentStatus' => 'required|array',
            'documentStatus.*.status' => ['sometimes', 'required', Rule::in(['Approved', 'Revisi', 'Ditolak'])], // <-- ATURAN BARU
            'documentStatus.*.note' => 'nullable|string|max:500',
            'vendor_type' => ['nullable', Rule::in(['vendorExpedisi', 'vendorTrade', 'vendorNonTrade', 'vendorDistributor', 'vendorClaimInternal'])],
        ]);
// ...

        $dbDocumentTypeMap = [
            'AktaPerusahaan' => 'Akta Perusahaan',
            'SPPKP' => 'SPPKP',
            'SKT' => 'SKT',
            'NPWP' => 'NPWP',
            'SIP_NIB' => 'SIP/NIB',
            'NomorRekening' => 'Nomor Rekening',
        ];

        foreach ($request->documentStatus as $key => $statusData) {
            $dbDocType = $dbDocumentTypeMap[$key] ?? null;
            if ($dbDocType) {
                VendorDocument::updateOrCreate(
                    ['vendor_id' => $vendor->id, 'document_type' => $dbDocType],
                    ['status' => $statusData['status'], 'note' => $statusData['note'] ?? null]
                );
            }
        }

        if ($request->filled('vendor_type')) {
            $vendor->vendor_type = $request->vendor_type;
            $vendor->save();
        }

        return response()->json(['message' => 'Validasi dokumen vendor berhasil diperbarui.'], 200);
    }

    /**
     * Hapus pengguna.
     * Endpoint: DELETE /api/admin/users/{id}
     */
    public function destroy(string $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Anda bukan admin.'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Tidak bisa menghapus akun Anda sendiri'], 403);
        }
        
        // Relasi akan otomatis menghapus vendor dan dokumen jika di-setup di Model/Migration
        // Namun, jika tidak di-setup, cara manual ini sudah benar.
        if ($user->vendor) {
            $user->vendor->documents()->delete();
            $user->vendor->delete();
        }
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus'], 200);
    }
}
// PERBAIKAN 2: Menghapus kurung kurawal ekstra