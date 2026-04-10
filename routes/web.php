<?php

use App\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset.form');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.reset');

Route::get('/reset-password/success', function () {
    return view('auth.reset-password-success');
})->name('password.reset.success');
