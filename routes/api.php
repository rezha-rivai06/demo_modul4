<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// CRUD Media API (parameters eksplisit agar route parameter tetap {media}, bukan {medium})
Route::apiResource('media', MediaController::class)->parameters([
    'media' => 'media',
]);

// Endpoint Pengguna & Koleksi Media (Custom Action M:N attach/detach)
Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::get('users/{user}/collection', [UserController::class, 'show']);
Route::post('users/{user}/collection', [UserController::class, 'addToCollection']);
Route::delete('users/{user}/collection/{media}', [UserController::class, 'removeFromCollection']);
