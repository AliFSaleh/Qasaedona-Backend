<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JoinRequestController;
use App\Http\Controllers\ResourceController;

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

//Auth
Route::post('login'  , [AuthController::class, 'login']);
Route::post('register' , [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout']);

Route::put('user', [AuthController::class, 'edit_profile']);
Route::get('user', [AuthController::class, 'get_profile']);
Route::delete('user/delete_account', [AuthController::class, 'delete_user']);

// join us as a poet
Route::post('verify-request', [JoinRequestController::class, 'verify_request']);
Route::post('verify-account' , [JoinRequestController::class, 'verify_account']);
Route::post('join-requests', [JoinRequestController::class, 'join_request']);

// resources
Route::get('countries', [ResourceController::class, 'get_countries']);
Route::get('occasions', [ResourceController::class, 'get_occasions']);
Route::get('rawadeds', [ResourceController::class, 'get_rawadeds']);
Route::get('poem_types', [ResourceController::class, 'get_poem_types']);
