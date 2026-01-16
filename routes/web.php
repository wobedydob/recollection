<?php

use Illuminate\Support\Facades\Route;

// Serve the SPA for all non-API routes
Route::get('/{any?}', function () {
    return view('spa');
})->where('any', '^(?!api).*$');
