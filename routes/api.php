<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth-actions');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth-actions');
});

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

Route::middleware(['api.auth', 'throttle:product-writes'])->group(function (): void {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
});
