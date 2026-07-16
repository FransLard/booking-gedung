<?php

use App\Http\Controllers\AddOnController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/booking/{gedung}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/bookings', [BookingController::class, 'myBookings'])->name('booking.my');
    Route::get('/booking/detail/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::get('/booking/{booking}/payment', [BookingController::class, 'paymentForm'])->name('booking.payment');
    Route::post('/booking/{booking}/payment', [BookingController::class, 'submitPayment'])->name('booking.payment.submit');
    Route::patch('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/admin/download-excel', [DashboardController::class, 'downloadExcel'])->name('admin.download-excel');
    Route::get('/admin/gedung/{gedung}', [DashboardController::class, 'gedung'])->name('admin.gedung');

    Route::get('/admin/bookings', [DashboardController::class, 'bookings'])->name('admin.bookings');
    Route::patch('/admin/bookings/{booking}/status', [DashboardController::class, 'updateStatus'])->name('admin.booking.status');
    Route::patch('/admin/bookings/{booking}/payment', [DashboardController::class, 'updatePayment'])->name('admin.booking.payment');
    Route::post('/admin/bookings/{booking}/verify', [DashboardController::class, 'verifyPayment'])->name('admin.booking.verify');

    Route::get('/admin/users', [DashboardController::class, 'users'])->name('admin.users');

    Route::get('/admin/addons', [AddOnController::class, 'index'])->name('admin.addons');
    Route::post('/admin/addons', [AddOnController::class, 'store'])->name('admin.addons.store');
    Route::put('/admin/addons/{addOn}', [AddOnController::class, 'update'])->name('admin.addons.update');
    Route::delete('/admin/addons/{addOn}', [AddOnController::class, 'destroy'])->name('admin.addons.destroy');

    Route::post('/admin/gedung/{gedung}/images', [DashboardController::class, 'uploadImage'])->name('admin.gedung.images.upload');
    Route::delete('/admin/gedung/images/{gedungImage}', [DashboardController::class, 'deleteImage'])->name('admin.gedung.images.delete');
    Route::put('/admin/gedung/{gedung}', [DashboardController::class, 'updateGedung'])->name('admin.gedung.update');
});
