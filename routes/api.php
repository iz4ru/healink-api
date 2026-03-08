<?php

use App\Http\Controllers\AuthController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth', 'check.role:admin'])->group(function () {

    });
    
});
