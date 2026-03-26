<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            $user->tokens()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Admin.',
                'type'    => 'ACCOUNT_DISABLED'
            ], 403);
        }

        return $next($request); // Lanjut ke middleware berikutnya
    }
}
