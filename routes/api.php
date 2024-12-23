<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JoinRequestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
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

// notification
Route::post('fcm_token', [NotificationController::class, 'register_token']);
Route::get('notifications', [NotificationController::class, 'get_notifications']);
Route::post('notifications/read', [NotificationController::class, 'notifications_read']);
Route::post('notifications/read/all', [NotificationController::class, 'notifications_read_all']);

// contact us
Route::post('messages', [MessageController::class, 'store']);

// resources
Route::get('countries', [ResourceController::class, 'get_countries']);
Route::get('occasions', [ResourceController::class, 'get_occasions']);
Route::get('rawadeds', [ResourceController::class, 'get_rawadeds']);
Route::get('poem_types', [ResourceController::class, 'get_poem_types']);
Route::get('categories', [ResourceController::class, 'get_categories']);
Route::get('languages', [ResourceController::class, 'get_languages']);
Route::get('lessons', [ResourceController::class, 'get_lessons']);
Route::get('poetry_collections', [ResourceController::class, 'get_poetry_collections']);
Route::get('pages', [ResourceController::class, 'get_pages']);
Route::get('sliders', [ResourceController::class, 'get_sliders']);
Route::get('media_constants', [ResourceController::class, 'get_media_constants']);
