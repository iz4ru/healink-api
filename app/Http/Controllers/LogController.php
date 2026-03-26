<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Log::with('user');

        if ($user->role === 'cashier' || 'admin') {
            $query->where('user_id', $user->id);
        } else {

        }
    }
}
