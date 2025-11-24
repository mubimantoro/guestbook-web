<?php

use App\Http\Controllers\Api\Admin\KategoriKunjunganController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Public\GuestController;
use App\Http\Controllers\Api\Public\TamuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::apiResource('/tamu', App\Http\Controllers\Api\Admin\TamuController::class);
    Route::apiResource('/kategori-kunjungan', KategoriKunjunganController::class);
    Route::get('/roles/all', [\App\Http\Controllers\Api\Admin\RoleController::class, 'all']);
    Route::apiResource('/roles', RoleController::class);
    Route::apiResource('/users', App\Http\Controllers\Api\Admin\UserController::class);
});




Route::prefix('public')->group(function () {
    Route::post('/tamu', [\App\Http\Controllers\Api\Public\TamuController::class, 'store']);
    Route::get('/kategori-kunjungan/all', [\App\Http\Controllers\Api\Public\KategoriKunjunganController::class, 'all']);
});
