<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;

// --- ROUTE PUBLIC (Bisa diakses tanpa login) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// --- ROUTE PROTECTED (Hanya bisa diakses jika punya Token JWT) ---
Route::middleware('auth:api')->group(function () {
    Route::post('/topup', [TransactionController::class, 'topup']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::get('/report', [TransactionController::class, 'report']);

    Route::middleware('auth:api')->group(function () {
    Route::put('/update-profile', [App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
});
});