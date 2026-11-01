<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\ParkingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected FirebaseService $firebase;
    protected ParkingService $parking;

    public function __construct(FirebaseService $firebase, ParkingService $parking)
    {
        $this->firebase = $firebase;
        $this->parking = $parking;
    }

    /**
     * Login untuk Admin, Petugas, Owner, dan Member.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $users = $this->firebase->get('tb_user') ?? [];
        $foundUser = null;
        $userKey = null;

        foreach ($users as $key => $u) {
            if (isset($u['username']) && strtolower($u['username']) === strtolower($validated['username'])) {
                $foundUser = $u;
                $userKey = $key;
                break;
            }
        }

        if (!$foundUser) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan.',
            ], 404);
        }

        if (empty($foundUser['status_aktif'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dinonaktifkan. Silakan hubungi Administrator.',
            ], 403);
        }

        // Cek hash password atau perbandingan langsung (untuk fleksibilitas pengujian UKK)
        $passwordMatches = Hash::check($validated['password'], $foundUser['password']) ||
            $validated['password'] === $foundUser['password'];

        if (!$passwordMatches) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi salah.',
            ], 401);
        }

        // Generate token sederhana untuk session
        $token = 'PKS-' . Str::random(40);
        $this->firebase->update("tb_user/{$userKey}", [
            'api_token' => $token,
            'last_login' => now()->toDateTimeString(),
        ]);

        // Catat aktivitas login
        $this->parking->logActivity(
            $foundUser['id_user'] ?? $foundUser['username'],
            "Pengguna {$foundUser['nama_lengkap']} ({$foundUser['role']}) berhasil login."
        );

        unset($foundUser['password']);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $foundUser,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Registrasi Akun Member Baru (Mobile App).
     */
    public function registerMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|min:4|max:50',
            'password' => 'required|string|min:6',
        ]);

        $users = $this->firebase->get('tb_user') ?? [];

        foreach ($users as $u) {
            if (isset($u['username']) && strtolower($u['username']) === strtolower($validated['username'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username sudah terdaftar.',
                ], 422);
            }
        }

        $userId = 'user_' . Str::lower(Str::random(8));
        $userData = [
            'id_user' => $userId,
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => strtolower($validated['username']),
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'status_aktif' => 1,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->firebase->set("tb_user/{$userId}", $userData);

        $this->parking->logActivity(
            $userId,
            "Member baru mendaftar: {$validated['nama_lengkap']} (@{$validated['username']})"
        );

        unset($userData['password']);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran akun member berhasil.',
            'data' => $userData,
        ], 201);
    }
}

