<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TodoListController;
use App\Http\Controllers\TodoItemController;
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

    // Memory Box - Ideas
    Route::prefix('memorybox')->group(function () {
        Route::get('/ideas', [IdeaController::class, 'index']);
        Route::post('/ideas', [IdeaController::class, 'store']);
        Route::put('/ideas/{id}', [IdeaController::class, 'update']);
        Route::delete('/ideas/{id}', [IdeaController::class, 'destroy']);

        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::put('/tags/{id}', [TagController::class, 'update']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);
    });

    // TODO Lists
    Route::prefix('todo')->group(function () {
        Route::get('/lists', [TodoListController::class, 'apiIndex']);
        Route::get('/lists/{list}', [TodoListController::class, 'apiShow']);
        Route::post('/lists', [TodoListController::class, 'store']);
        Route::put('/lists/{list}', [TodoListController::class, 'update']);
        Route::delete('/lists/{list}', [TodoListController::class, 'destroy']);

        // TODO Items
        Route::get('/lists/{list}/items', [TodoItemController::class, 'index']);
        Route::post('/lists/{list}/items', [TodoItemController::class, 'store']);
        Route::put('/items/{item}', [TodoItemController::class, 'update']);
        Route::delete('/items/{item}', [TodoItemController::class, 'destroy']);
        Route::patch('/items/{item}/toggle', [TodoItemController::class, 'toggle']);
    });
});
