<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VendorProfileController;
use App\Http\Controllers\AdminUserController;

// Rute untuk autentikasi (registrasi & login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute yang membutuhkan autentikasi (menggunakan middleware auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk mendapatkan info user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute-rute untuk Vendor Profile (Vendor view)
    Route::get('/vendor/profile', [VendorProfileController::class, 'show']);
    Route::post('/vendor/profile/save', [VendorProfileController::class, 'save']);
    Route::post('/vendor/documents/{documentType}', [VendorProfileController::class, 'uploadDocument']);

    // Rute-rute Admin User Management & Vendor Validation
    Route::get('/admin/users', [AdminUserController::class, 'index']); // Untuk daftar user/vendor
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']); // Menghapus user

    // --- Rute BARU untuk Validasi Vendor (Admin) ---
    // {id} di sini adalah user_id dari user vendor yang akan divalidasi
    Route::get('/admin/vendors/{id}/validate', [AdminUserController::class, 'showVendorForValidation']);
    Route::post('/admin/vendors/{id}/validate', [AdminUserController::class, 'updateVendorValidation']);
});

