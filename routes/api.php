<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChecklistItemController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);
    Route::patch('/auth/theme', [AuthController::class, 'updateTheme']);
    Route::patch('/auth/color-theme', [AuthController::class, 'updateColorTheme']);

    // Memory Box - Ideas
    Route::prefix('memory-box')->group(function () {
        Route::get('/ideas', [IdeaController::class, 'index']);
        Route::post('/ideas', [IdeaController::class, 'store']);
        Route::put('/ideas/{id}', [IdeaController::class, 'update']);
        Route::delete('/ideas/{id}', [IdeaController::class, 'destroy']);

        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::put('/tags/{id}', [TagController::class, 'update']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);
    });

    // Checklist
    Route::prefix('checklist')->group(function () {
        Route::get('/lists', [ChecklistController::class, 'apiIndex']);
        Route::get('/lists/{list}', [ChecklistController::class, 'apiShow']);
        Route::post('/lists', [ChecklistController::class, 'store']);
        Route::put('/lists/{list}', [ChecklistController::class, 'update']);
        Route::delete('/lists/{list}', [ChecklistController::class, 'destroy']);

        // Checklist Items
        Route::get('/lists/{list}/items', [ChecklistItemController::class, 'index']);
        Route::post('/lists/{list}/items', [ChecklistItemController::class, 'store']);
        Route::put('/items/{item}', [ChecklistItemController::class, 'update']);
        Route::delete('/items/{item}', [ChecklistItemController::class, 'destroy']);
        Route::patch('/items/{item}/toggle', [ChecklistItemController::class, 'toggle']);
    });
});
