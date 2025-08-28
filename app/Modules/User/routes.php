<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\UserController;

// User management routes (protected)
Route::middleware('firebase.auth')->group(function () {
    Route::prefix('api/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('api.users.index');
        Route::get('/search', [UserController::class, 'search'])->name('api.users.search');
        Route::get('/{id}', [UserController::class, 'show'])->name('api.users.show');
        Route::put('/{id}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
    });
});