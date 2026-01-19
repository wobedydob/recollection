<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
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
    Route::get('/memory-box', function () {
        return view('ideas.index');
    })->name('memorybox.index');

    // Checklist
    Route::get('/checklist', [ChecklistController::class, 'index'])->name('checklist.index');
    Route::get('/checklist/{list}', [ChecklistController::class, 'show'])->name('checklist.show');

    // Suggestions
    Route::get('/suggesties', function () {
        return view('suggestions.index');
    })->name('suggestions.index');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'webUpdateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'webUpdatePassword'])->name('profile.password');
    Route::delete('/profile', [AuthController::class, 'webDeleteAccount'])->name('profile.delete');
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Suggestions
    Route::get('/suggestions', [AdminController::class, 'suggestions'])->name('suggestions');
    Route::patch('/suggestions/{suggestion}/status', [AdminController::class, 'updateSuggestionStatus'])->name('suggestions.status');
    Route::delete('/suggestions/{suggestion}', [AdminController::class, 'deleteSuggestion'])->name('suggestions.delete');
});
