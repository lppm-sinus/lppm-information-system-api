<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// AUTH
Route::post('/users/login', [UserController::class, 'login']);

// PAGES
Route::get('/pages/menu', [PageController::class, 'getPagesMenu']);

// POSTS
Route::get('/posts/{page_slug}', [PostController::class, 'getPostsByPageSlug']);

Route::middleware('auth:sanctum')->group(function () {
    // AUTH
    Route::post('/users', [UserController::class, 'register']);
    Route::get('/users', [UserController::class, 'getUserList']);
    Route::get('/users/current', [UserController::class, 'getCurrentUser']);
    Route::patch('/users/current', [UserController::class, 'updateCurrentUser']);
    Route::patch('/users/{id}', [UserController::class, 'updateUserByID'])->where('id', '[0-9]+');
    Route::get('/users/{id}', [UserController::class, 'getUserByID'])->where('id', '[0-9]+');
    Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->where('id', '[0-9]+');
    Route::post('/users/logout', [UserController::class, 'logout']);

    // PAGES
    Route::post('/pages', [PageController::class, 'create']);
    Route::patch('/pages/{id}', [PageController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/pages/{id}', [PageController::class, 'getPageByID'])->where('id', '[0-9]+');
    Route::get('/pages', [PageController::class, 'getPages']);
    Route::delete('/pages/{id}', [PageController::class, 'delete'])->where('id', '[0-9]+');

    // CATEGORIES
    Route::post('/categories', [CategoryController::class, 'create']);
    Route::patch('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/categories/{id}', [CategoryController::class, 'getCategoryByID'])->where('id', '[0-9]+');
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::delete('/categories/{id}', [CategoryController::class, 'delete'])->where('id', '[0-9]+');

    // POSTS
    Route::post('/posts', [PostController::class, 'create']);
    Route::get('/posts', [PostController::class, 'getPosts']);
    Route::get('/post/{id}', [PostController::class, 'getPostByID'])->where('id', '[0-9]+');
    Route::patch('/post/{id}', [PostController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/post/{id}', [PostController::class, 'delete'])->where('id', '[0-9]+');
});