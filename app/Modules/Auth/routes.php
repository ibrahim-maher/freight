<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;

// Auth pages
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

// API endpoints
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.login'); // Add this line
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/verify-token', [AuthController::class, 'verifyToken'])->name('api.verify');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    
    // Protected routes
    Route::middleware('firebase.auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('api.profile');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
    });
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});