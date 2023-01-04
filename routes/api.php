<?php

use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthUserContoller;
use App\Http\Controllers\CountryController;
use App\Http\Response\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/user/login', [AuthUserContoller::class, 'register']);
Route::get('/countries', [CountryController::class, 'index']);

Route::post('/admin/login', [AuthAdminController::class, 'login']);
