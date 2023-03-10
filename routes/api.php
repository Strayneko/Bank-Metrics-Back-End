<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthUserContoller;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PasswordResets;
use App\Http\Response\BaseResponse;
use Illuminate\Support\Facades\Auth;

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

Route::middleware(['auth:sanctum', 'hasApiKey'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', fn () => BaseResponse::success(message: 'Metrics API'));

// auth grouping
Route::prefix('auth')
    ->controller(AuthUserContoller::class)
    ->group(function () {
        Route::post('/register',  'register');
        Route::get('/index', 'index');
        Route::post('/login',  'login')->name('login');
        Route::post('/logout',  'logout')->middleware("auth:sanctum");
        Route::get('/verifications/{confirmation_code}',  'verification');
    });

// list country
Route::get('/countries', [CountryController::class, 'index']);


Route::post('/passwordReset', [PasswordResets::class, 'password_reset']);
Route::post('/resetPassword/{token}', [PasswordResets::class, 'reset']);

Route::prefix('admin')
    ->middleware(["auth:sanctum", 'isAdmin', 'hasApiKey'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/show/{id}', [AdminController::class, 'show']);
        Route::post('/', [AdminController::class, 'store']);
        Route::post('/edit/{id}', [AdminController::class, 'update']);
        // Route::post('/delete/{id}', [AdminController::class,'destroy']);
    });

Route::prefix('user')
    ->middleware(['auth:sanctum', 'hasApiKey'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/me', [UserController::class, 'show']);
        Route::get('/profile', [UserController::class, 'index_profile']);
        Route::post('/me', [UserController::class, 'store_profile']);
        // Route::post('/delete/{id}', [UserController::class,'destroy']);
    });

Route::prefix('bank')->middleware(["auth:sanctum", 'hasApiKey'])->group(function () {
    Route::get('/', [BankController::class, 'index']);
    Route::get('/trash', [BankController::class, 'deletedindex']);
    Route::get('/restore/{id}', [BankController::class, 'restore']);
    Route::get('/show/{id}', [BankController::class, 'show']);
    Route::post('/create', [BankController::class, 'store']);
    Route::post('/edit/{id}', [BankController::class, 'update']);
    Route::post('/delete/{id}', [BankController::class, 'destroy']);
});

// loan group prefix
Route::prefix('loan')
    ->controller(LoanController::class)
    ->middleware(['auth:sanctum', 'hasApiKey'])
    ->group(function () {
        Route::post('/get_loan', 'loan');
        Route::get('/list/', 'list_loan');
        Route::get('/rejection_reason/{loan_id}', 'rejection_reason');
        Route::get('/all', 'index');
    });
