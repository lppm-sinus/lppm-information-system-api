<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/users/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users', [UserController::class, 'register']);
    Route::get('/users', [UserController::class, 'getUserList']);
    Route::get('/users/current', [UserController::class, 'getCurrentUser']);
    Route::patch('/users/current', [UserController::class, 'updateCurrentUser']);
    Route::patch('/users/{id}', [UserController::class, 'updateUser'])->where('id', '[0-9]+');
    Route::get('/users/{id}', [UserController::class, 'getUser'])->where('id', '[0-9]+');
    Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->where('id', '[0-9]+');
    Route::post('/users/logout', [UserController::class, 'logout']);
});