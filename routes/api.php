<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Models\Admin;
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

// User Routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Items Routes
Route::prefix('/item')->middleware('UserVerified')->controller(ItemsController::class)->group(function () {
    Route::get('/alldata', 'index');
    Route::get('/allcategory', 'create');
    Route::get('/showone/{id}', 'show');
    Route::post('/create', 'store');
    Route::delete('/delete/{id}', 'delete');
    Route::post('/updatedata/{old_id}', 'update');
    Route::get('/expire', 'expire');
    Route::get('/soonexpire', 'soon_expire');
    Route::get('/quantity', 'sumQuantitiesByTitle');

    // Search-related Routes
    Route::post('/search', 'search');
    Route::post('/searchexpire', 'searchExpire');
    Route::post('/searchsoonexpire', 'searchSoonExpire');
    Route::post('/searchdate', 'searchdate');
});

// Admin Routes
Route::middleware('UserVerified')->get('index', [AdminController::class, 'index']);

// Profile Routes
Route::prefix('/profile')->middleware('UserVerified')->controller(UserController::class)->group(function () {
    Route::post('updateprofile', 'UpdateProfile');
    Route::get('ProfileUser', 'Profile');
});

// Auth Routes
Route::prefix('/user')->controller(UserController::class)->group(function () {
    Route::post('Register', 'Register');
    Route::post('Login', 'Login');
    Route::post('Logout', 'Logout');
});

// Forget Password Routes
Route::post('emailExists', [ForgetPasswordController::class, 'emailExists']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('sendCode', [ForgetPasswordController::class, 'sendCode']);
    Route::post('checkCode', [ForgetPasswordController::class, 'checkCode']);
});
Route::post('resetPassword', [ForgetPasswordController::class, 'resetPassword'])->middleware('UserVerified');

// Admin Auth Routes
Route::prefix('admin')->namespace('App\Http\Controllers')->group(function () {
    Route::post('login', 'AdminController@login');
    Route::middleware(['auth.guard:admin-api'])->group(function () {
        Route::post('logout', 'AdminController@logout');
        Route::delete('deleteUser/{id}', 'AdminController@deleteUser');
        Route::get('countUsers', 'AdminController@countUsers');
        Route::get('countItems', 'AdminController@countItems');
        Route::get('countCategories', 'AdminController@countCategories');
        Route::get('getAllUsers', 'AdminController@getAllUsers');
        Route::get('latestUsers', 'AdminController@latestUsers');
        Route::get('latestItems', 'AdminController@latestItems');
        Route::get('index', 'AdminController@index'); 
    });
});

// Admin Profile Routes
Route::prefix('/profile')->middleware('auth.guard:admin-api')->controller(AdminController::class)->group(function () {
    Route::get('ProfileAdmin', 'Profile');
});

// Admin Items Routes
Route::prefix('admin')->namespace('App\Http\Controllers')->middleware(['auth.guard:admin-api'])->group(function () {
    Route::get('alldata', 'ItemsController@index');
    Route::get('allcategory', 'ItemsController@create');
    Route::get('showone/{id}', 'ItemsController@show');
    Route::post('store', 'ItemsController@store');
    Route::post('/updatedata/{old_id}', 'ItemsController@update');
    Route::delete('delete/{id}', 'ItemsController@delete');
    Route::delete('expire', 'ItemsController@expire');
    Route::delete('soon_expire', 'ItemsController@soon_expire');
    Route::delete('quantity', 'ItemsController@sumQuantitiesByTitle');
});

// Admin Category Routes
Route::get('getCategoriesWithItems', [CategoryController::class, 'getCategoriesWithItems'])->middleware(['auth.guard:admin-api']);
