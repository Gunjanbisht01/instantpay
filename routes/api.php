<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
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

// Unauthenticated Route
Route::any('', function () {
    return response(['message' => 'Please provide access token'], 401);
})->name('error');

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login')->name('login');
});

Route::middleware('auth:sanctum')->group(function () {
    //get all users
    Route::get('/users', [UserController::class, 'getUsers']);
    //show authenticated user data
    Route::get('/user', [UserController::class, 'show']);
    //create user
    Route::post('/user', [UserController::class, 'addUser']);

    //board routes
    Route::resource('boards', BoardController::class);

    // specific board tasks
    //Route::get('/board/tasks', [TaskController::class, 'boardTask']);

    //task routes
    Route::resource('tasks', TaskController::class);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
