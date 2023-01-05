<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthUserContoller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user/register', [AuthUserContoller::class, 'register']);
Route::post('/user/login', [AuthUserContoller::class, 'login']);
Route::get('/countries', [CountryController::class, 'index']);

Route::post('/admin/login', [AuthAdminController::class, 'login']);
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/{id}/show', [AdminController::class, 'show']);
    Route::post('/', [AdminController::class, 'store']);
    Route::post('/edit/{id}', [AdminController::class, 'update']);
    // Route::post('/delete/{id}', [AdminController::class,'destroy']);
});

Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}/show', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::post('/edit/{id}', [UserController::class, 'update']);
    // Route::post('/delete/{id}', [UserController::class,'destroy']);
});

Route::prefix('bank')->group(function () {
    Route::get('/', [BankController::class, 'index']);
    Route::get('/{id}/show', [BankController::class, 'show']);
    Route::post('/', [BankController::class, 'store']);
    Route::post('/edit/{id}', [BankController::class, 'update']);
    // Route::post('/delete/{id}', [BankController::class,'destroy']);
});
