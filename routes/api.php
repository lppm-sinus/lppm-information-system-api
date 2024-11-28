<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudyProgramController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// AUTH
Route::post('/users/login', [UserController::class, 'login']);

// PAGES
Route::get('/pages/menu', [PageController::class, 'getPagesMenu']);

// POSTS
Route::get('/posts/by-page/{page_slug}', [PostController::class, 'getPostsByPageSlug']);

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
    Route::get('/posts/{id}', [PostController::class, 'getPostByID'])->where('id', '[0-9]+');
    Route::patch('/posts/{id}', [PostController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/posts/{id}', [PostController::class, 'delete'])->where('id', '[0-9]+');

    // AUTHORS
    Route::post('/authors/import', [AuthorController::class, 'import']);
    Route::post('/authors', [AuthorController::class, 'create']);
    Route::patch('/authors/{id}', [AuthorController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/authors/{id}', [AuthorController::class, 'getAuthorByID'])->where('id', '[0-9]+');
    Route::get('/authors', [AuthorController::class, 'getAuthors']);

    // STUDY PROGRAMS
    Route::post('/study-programs', [StudyProgramController::class, 'create']);
    Route::patch('/study-programs/{id}', [StudyProgramController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/study-programs/{id}', [StudyProgramController::class, 'getStudyProgramByID'])->where('id', '[0-9]+');
    Route::get('/study-programs', [StudyProgramController::class, 'getStudyPrograms']);
    Route::delete('/study-programs/{id}', [StudyProgramController::class, 'delete'])->where('id', '[0-9]+');

    // RESEARCHES
    Route::post('/researches/import', [ResearchController::class, 'import']);
    Route::post('/researches', [ResearchController::class, 'create']);
    Route::patch('/researches/{id}', [ResearchController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/researches', [ResearchController::class, 'getResearches']);
    Route::get('/researches/{id}', [ResearchController::class, 'getResearchByID'])->where('id', '[0-9]+');
    Route::delete('/researches/{id}', [ResearchController::class, 'delete'])->where('id', '[0-9]+');
    Route::get('/researches/grouped-by-scheme', [ResearchController::class, 'getResearchesGroupedByScheme']);
    Route::get('/researches/chart-data', [ResearchController::class, 'getResearchesChartData']);

    // SERVICES
    Route::post('/services/import', [ServiceController::class, 'import']);
    Route::post('/services', [ServiceController::class, 'create']);
    Route::patch('/services/{id}', [ServiceController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/services', [ServiceController::class, 'getServices']);
    Route::get('/services/{id}', [ServiceController::class, 'getServiceByID'])->where('id', '[0-9]+');
    Route::get('/services/grouped-by-scheme', [ServiceController::class, 'getServicesGroupedByScheme']);
    Route::delete('/services/{id}', [ServiceController::class, 'delete'])->where('id', '[0-9]+');
    Route::get('/services/chart-data', [ServiceController::class, 'getServicesChartData']);
});

