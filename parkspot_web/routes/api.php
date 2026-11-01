<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - ParkSpot UKK Paket 2
|--------------------------------------------------------------------------
*/

// --- Autentikasi ---
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register-member', [AuthController::class, 'registerMember']);
});

// --- Modul Parkir & Transaksi (Petugas, Guest, Member) ---
Route::prefix('parking')->group(function () {
    Route::get('/slots', [ParkingController::class, 'getSlots']);
    Route::get('/areas', [ParkingController::class, 'getAreas']);
    Route::get('/tariffs', [ParkingController::class, 'getTariffs']);
    Route::post('/gate-in', [ParkingController::class, 'gateIn']);
    Route::get('/ticket/{kodeTiket}', [ParkingController::class, 'getTicketStatus']);
    Route::post('/gate-out', [ParkingController::class, 'gateOut']);
    Route::post('/booking', [ParkingController::class, 'bookSlot']);
});

// --- Modul Pembayaran QRIS Midtrans ---
Route::prefix('payment')->group(function () {
    Route::post('/create-qris', [PaymentController::class, 'createQris']);
    Route::post('/midtrans-callback', [PaymentController::class, 'handleCallback']);
    Route::post('/simulate-success/{idParkir}', [PaymentController::class, 'simulateSuccess']);
});

// --- Modul Master Data & Log Audit (Admin) ---
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'saveUser']);
    Route::delete('/users/{idUser}', [AdminController::class, 'deleteUser']);
    Route::put('/tariffs/{key}', [AdminController::class, 'updateTariff']);
    Route::get('/logs', [AdminController::class, 'getLogs']);
});

// --- Modul Dashboard & Laporan Finansial (Owner) ---
Route::prefix('owner')->group(function () {
    Route::get('/summary', [AdminController::class, 'getOwnerSummary']);
    Route::get('/reports', [AdminController::class, 'getReports']);
});
