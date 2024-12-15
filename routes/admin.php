<?php

use App\Http\Controllers\Admin\OccasionController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
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

// roles and permissions
Route::get('/permissions', [PermissionController::class, 'get_all_permissions']);
Route::get('/permissions/me', [PermissionController::class, 'my_permissions']);
Route::get('/roles/{role}/permissions', [PermissionController::class, 'get_permissions']);
Route::post('/roles/{role}/permissions', [PermissionController::class, 'set_permissions']);
Route::apiResource('roles', RoleController::class);

// users
Route::apiResource('users' , UserController::class);
Route::get('users/export/{type}', [UserController::class, 'export']);
Route::post('users/{user}/reset_password', [UserController::class, 'reset_password']);
Route::post('users/{user}/profile_activate', [UserController::class, 'user_profile_status_toggle']);
Route::post('users/{user}/activate', [UserController::class, 'user_status_toggle']);

// occasions
Route::apiResource('occasions' , OccasionController::class);
Route::get('occasions/export/{type}', [OccasionController::class, 'export']);
Route::post('occasions/{occasion}/activate', [OccasionController::class, 'occasion_status_toggle']);