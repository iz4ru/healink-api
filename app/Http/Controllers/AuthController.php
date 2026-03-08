<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // VALIDASI
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginValue = $request->input('login');
        $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        // CEK USER ADA
        $user = User::where($loginField, $loginValue)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Akun tidak ditemukan.',
            ], 404);
        }

        // CEK AKUN AKTIF
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Silakan hubungi owner / admin.',
            ], 403);
        }

        // CEK PASSWORD
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
            ], 401);
        }

        // BUAT TOKEN (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'username' => $user->username,
                'role'     => $user->role,
            ],
        ], 200);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        // $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

        $bearerToken = $request->bearerToken(); // "1|abc123"
        $tokenId = explode('|', $bearerToken, 2)[0]; // "1"
        
        $request->user()->tokens()->where('id', $tokenId)->delete();
            
        return response()->json([
            'message' => 'Logout berhasil.',
        ], 200);
    }
}
