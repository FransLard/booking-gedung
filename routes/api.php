<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\GedungController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/gedung', [GedungController::class, 'index']);
Route::get('/gedung/{gedung}', [GedungController::class, 'show']);
Route::get('/gedung/{gedung}/available-dates', [BookingController::class, 'checkAvailableDates']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/booking', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'myBookings']);
    Route::get('/booking/{booking}', [BookingController::class, 'show']);
    Route::post('/booking/{booking}/payment', [BookingController::class, 'submitPayment']);
    Route::patch('/booking/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});
