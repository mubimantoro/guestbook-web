<?php

use App\Http\Controllers\Api\Admin\KategoriKunjunganController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Public\GuestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::apiResource('/guests', App\Http\Controllers\Api\Admin\GuestController::class);
    Route::apiResource('/kategori-kunjungan', KategoriKunjunganController::class);
    Route::get('/roles/all', [\App\Http\Controllers\Api\Admin\RoleController::class, 'all']);
    Route::apiResource('/roles', RoleController::class);
    Route::apiResource('/users', App\Http\Controllers\Api\Admin\UserController::class);
});


Route::post('/guests', [GuestController::class, 'store']);
