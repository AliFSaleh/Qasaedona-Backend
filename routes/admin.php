<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\JoinRequestController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\OccasionController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PoemController;
use App\Http\Controllers\Admin\PoemTypeController;
use App\Http\Controllers\Admin\PoetryCollectionController;
use App\Http\Controllers\Admin\RawadedController;
use App\Http\Controllers\Admin\RejectReasonController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SliderController;
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

// join us as a poet
Route::get('join_requests', [JoinRequestController::class, 'index']);
Route::get('join_requests/export/{type}', [JoinRequestController::class, 'export']);
Route::post('join_requests/{join_request}/approve', [JoinRequestController::class, 'approve']);
Route::post('join_requests/{join_request}/reject', [JoinRequestController::class, 'reject']);

// rawaded
Route::apiResource('rawadeds' , RawadedController::class);
Route::get('rawadeds/export/{type}', [RawadedController::class, 'export']);
Route::post('rawadeds/{rawaded}/featured', [RawadedController::class, 'rawaded_feature_toggle']);
Route::post('rawadeds/{rawaded}/activate', [RawadedController::class, 'rawaded_status_toggle']);

// poem type
Route::apiResource('poem_types' , PoemTypeController::class);
// poem categories
Route::apiResource('categories' , CategoryController::class);
// poem languages
Route::apiResource('languages' , LanguageController::class);

// reject reasons
Route::apiResource('reject_reasons' , RejectReasonController::class);

// poems
Route::apiResource('poems' , PoemController::class);
Route::post('poems/{poem}/activate', [PoemController::class, 'poems_status_toggle']);

// poetry collections
Route::apiResource('poetry_collections' , PoetryCollectionController::class);
Route::post('poetry_collections/{poetry_collection}/activate', [PoetryCollectionController::class, 'poetry_collections_status_toggle']);

// lessons
Route::apiResource('lessons' , LessonController::class);
Route::post('lessons/{lesson}/activate', [LessonController::class, 'lessonss_status_toggle']);

//pages
Route::get('pages', [PageController::class, 'index']);
Route::post('pages/{page}', [PageController::class, 'update']);

//banners
Route::apiResource('sliders', SliderController::class);