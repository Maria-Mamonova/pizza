<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;

// Гостевые и общие маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ping', fn () => response()->json(['pong' => true]));
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'addOrUpdate']);

// Только для авторизованных пользователей
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Только для админов
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/orders', [AdminController::class, 'index']);
    Route::get('/admin/orders/{order}', [AdminController::class, 'show']);
    Route::post('/admin/orders/{order}/status', [AdminController::class, 'updateStatus']);
    Route::delete('/admin/orders/{order}', [AdminController::class, 'destroy']);
    Route::apiResource('/admin/products', ProductController::class);
});
