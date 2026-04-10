<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar di sistem.',
        ]);

        $status = Password::sendResetLink($request->only('email'));
        $throttleSeconds = config('auth.passwords.users.throttle', 60);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email Anda.',
                'retry_after' => $throttleSeconds,
            ], 200);
        }

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'success' => false,
                'message' => "Silakan tunggu {$throttleSeconds} detik sebelum meminta lagi.",
                'retry_after' => $throttleSeconds,
            ], 429);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim email reset password. Silakan coba beberapa saat lagi.',
        ], 400);
    }
}
