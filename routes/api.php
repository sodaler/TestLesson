<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/posts', [\App\Http\Controllers\Api\Post\PostController::class, 'index']);
Route::post('/posts', [\App\Http\Controllers\Api\Post\PostController::class, 'store']);
Route::patch('/posts/{post}', [\App\Http\Controllers\Api\Post\PostController::class, 'update']);
Route::get('/posts/{post}', [\App\Http\Controllers\Api\Post\PostController::class, 'show']);
Route::delete('/posts/{post}', [\App\Http\Controllers\Api\Post\PostController::class, 'delete'])->middleware('auth');
