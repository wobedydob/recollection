<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Guest routes (login, register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'webLogin']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'webRegister']);

    // Forgot password
    Route::get('/password/forgot', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');
});

// Password reset (accessible by anyone with a valid token)
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Email verification routes (auth required, but NOT verified)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');
});

// Authenticated AND verified routes
Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::delete('/profile', [AuthController::class, 'webDeleteAccount'])->name('profile.delete');

    // Password reset request (from profile)
    Route::post('/password/send-reset-link', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')
        ->name('password.send-reset-link');
});

// Admin routes (auth + verified + admin)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
