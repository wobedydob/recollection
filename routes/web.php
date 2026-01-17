<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Guest routes (login, register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'webLogin']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'webRegister']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('ideas.index');
    });
    Route::get('/ideas', function () {
        return view('ideas.index');
    })->name('ideas.index');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'webUpdateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'webUpdatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');
});
