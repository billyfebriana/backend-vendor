<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Tambahkan ini untuk fasilitas autentikasi

class AuthController extends Controller
{
    /**
     * Handle user registration.
     * Membuat user baru (Admin atau Vendor).
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => ['required', 'string', Rule::in(['admin', 'vendor'])],
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'vendor_id' => null,
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle user login and token generation.
     * Mengotentikasi user dan memberikan API token.
     */
    public function login(Request $request)
    {
        try {
            // 1. Validasi kredensial login
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // 2. Coba autentikasi user
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401); // Kode status 401 Unauthorized
            }

            // 3. Jika autentikasi berhasil, ambil user yang login
            $user = Auth::user();

            // 4. Hasilkan token API menggunakan Laravel Sanctum
            // Nama token bisa disesuaikan, misal 'auth_token'
            $token = $user->createToken('auth_token')->plainTextToken;

            // 5. Berikan respons sukses dengan token dan data user
            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    // Kita tidak mengirim password atau hash-nya
                ],
                'access_token' => $token,
                'token_type' => 'Bearer', // Tipe token, standar untuk API
            ], 200); // Kode status 200 OK

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani error validasi
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

