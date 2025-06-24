<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Models\PurchaseOrder; // Pastikan model ini ada
use App\Models\GoodsReceiptNote; // Pastikan model ini ada
use App\Models\Invoice; // Pastikan model ini ada

class DashboardController extends Controller
{
    /**
     * Mengambil data dashboard berdasarkan role user yang sedang login.
     * Endpoint: GET /api/dashboard
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Tidak terautentikasi.'], 401);
            }

            $dashboardData = [];

            if ($user->role === 'admin') {
                // --- Data untuk Admin Dashboard ---
                $dashboardData['total_users'] = User::count();
                $dashboardData['total_admins'] = User::where('role', 'admin')->count();
                $dashboardData['total_vendors'] = User::where('role', 'vendor')->count();

                // Jumlah vendor yang sudah mengisi profil (punya entri di tabel vendors)
                $dashboardData['vendors_with_profile'] = Vendor::count();
                // Jumlah vendor yang belum mengisi profil (user role vendor tapi vendor_id null)
                $dashboardData['vendors_without_profile'] = User::where('role', 'vendor')->whereNull('vendor_id')->count();

                // Status dokumen vendor
                $dashboardData['vendor_documents_pending'] = VendorDocument::where('status', 'Pending')->count();
                $dashboardData['vendor_documents_approved'] = VendorDocument::where('status', 'Approved')->count();
                $dashboardData['vendor_documents_revision'] = VendorDocument::where('status', 'Revisi')->count();
                $dashboardData['vendor_documents_rejected'] = VendorDocument::where('status', 'Ditolak')->count();

                // Contoh data ringkasan lain untuk admin (jika ingin melihat agregat PO, GRN, Invoice semua vendor)
                $dashboardData['total_pos'] = PurchaseOrder::count();
                $dashboardData['total_grns'] = GoodsReceiptNote::count();
                $dashboardData['total_invoices_submitted'] = Invoice::where('status', 'Submitted')->count();
                $dashboardData['total_invoices_approved'] = Invoice::where('status', 'Approved')->count();

                $dashboardData['recent_activities'] = [
                    // Ini bisa diisi dengan user terbaru, dokumen terbaru, dll.
                    // Contoh: User::latest()->take(5)->get(['id', 'email', 'role', 'created_at']),
                    // Contoh: VendorDocument::latest()->take(5)->get(['id', 'document_type', 'status', 'created_at']),
                ];


            } elseif ($user->role === 'vendor') {
                // --- Data untuk Vendor Dashboard ---
                $vendor = Vendor::where('user_id', $user->id)->first();

                if (!$vendor) {
                    // Jika vendor belum mengisi profil, dashboard akan minim informasi
                    return response()->json([
                        'message' => 'Profil vendor Anda belum lengkap. Silakan lengkapi profil untuk melihat detail dashboard.',
                        'user_email' => $user->email,
                        'is_profile_incomplete' => true,
                        'dashboard_info' => []
                    ], 200);
                }

                $dashboardData['vendor_id'] = $vendor->id;
                $dashboardData['company_name'] = $vendor->company_name;

                // Jumlah PO untuk vendor ini
                $dashboardData['total_pos'] = PurchaseOrder::where('vendor_id', $vendor->id)->count();
                // Jumlah GRN untuk vendor ini
                $dashboardData['total_grns'] = GoodsReceiptNote::where('vendor_id', $vendor->id)->count();

                // Status Invoice untuk vendor ini
                $dashboardData['invoices_pending'] = Invoice::where('vendor_id', $vendor->id)->where('status', 'Pending')->count();
                $dashboardData['invoices_submitted'] = Invoice::where('vendor_id', $vendor->id)->where('status', 'Submitted')->count();
                $dashboardData['invoices_approved'] = Invoice::where('vendor_id', $vendor->id)->where('status', 'Approved')->count();
                $dashboardData['invoices_rejected'] = Invoice::where('vendor_id', $vendor->id)->where('status', 'Rejected')->count();
                $dashboardData['invoices_paid'] = Invoice::where('vendor_id', $vendor->id)->where('status', 'Paid')->count();


                // Status Dokumen Vendor ini
                $dashboardData['documents_pending'] = VendorDocument::where('vendor_id', $vendor->id)->where('status', 'Pending')->count();
                $dashboardData['documents_approved'] = VendorDocument::where('vendor_id', $vendor->id)->where('status', 'Approved')->count();
                $dashboardData['documents_revision'] = VendorDocument::where('vendor_id', $vendor->id)->where('status', 'Revisi')->count();
                $dashboardData['documents_rejected'] = VendorDocument::where('vendor_id', $vendor->id)->where('status', 'Ditolak')->count();

                $dashboardData['recent_pos'] = PurchaseOrder::where('vendor_id', $vendor->id)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get(['id', 'po_number', 'po_date', 'status']);
                $dashboardData['recent_invoices'] = Invoice::where('vendor_id', $vendor->id)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get(['id', 'invoice_number', 'invoice_date', 'status', 'total_invoice_paid']);

            } else {
                return response()->json(['message' => 'Role pengguna tidak dikenali.'], 403);
            }

            return response()->json([
                'user_role' => $user->role,
                'user_email' => $user->email,
                'dashboard_info' => $dashboardData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching dashboard data: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data dashboard', 'error' => $e->getMessage()], 500);
        }
    }
}
