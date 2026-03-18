<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('check.role:admin')->group(function () {

        # Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/create/product', [ProductController::class, 'store']);
        Route::get('/categories', [ProductController::class, 'indexCategory']);
        Route::get('/units', [ProductController::class, 'indexUnit']);

        // CRUD Kategori
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // CRUD Unit (Jenis Satuan)
        Route::post('/units', [UnitController::class, 'store']);
        Route::put('/units/{id}', [UnitController::class, 'update']);
        Route::delete('/units/{id}', [UnitController::class, 'destroy']);

        // CRUD Batch produk
        Route::put('/batches/{id}', [ProductBatchController::class, 'update']);
        Route::delete('/batches/{id}', [ProductBatchController::class, 'destroy']);

    });
    
});
