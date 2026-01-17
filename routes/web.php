<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoListController;
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
    // Home
    Route::get('/', function () {
        return view('home');
    })->name('home');

    // Memory Box
    Route::get('/memorybox', function () {
        return view('ideas.index');
    })->name('memorybox.index');

    // TODO
    Route::get('/todo', [TodoListController::class, 'index'])->name('todo.index');
    Route::get('/todo/{list}', [TodoListController::class, 'show'])->name('todo.show');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'webUpdateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'webUpdatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');
});
