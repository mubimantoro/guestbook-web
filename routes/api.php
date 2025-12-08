<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\KategoriKunjunganController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Public\GuestController;
use App\Http\Controllers\Api\Public\TamuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/kunjungan-per-kategori', [DashboardController::class, 'kunjunganPerKategori']);
        Route::get('/kunjungan-per-instansi', [DashboardController::class, 'kunjunganPerInstansi']);
        Route::get('/trend-bulanan', [DashboardController::class, 'trendKunjunganBulanan']);
        Route::get('/trend-mingguan', [DashboardController::class, 'trendKunjunganMingguan']);
        Route::get('/distribusi-status', [DashboardController::class, 'distribusiStatus']);
        Route::get('/kunjungan-per-pic', [DashboardController::class, 'kunjunganPerPIC']);
        Route::get('/recent-visitors', [DashboardController::class, 'recentVisitors']);
        Route::get('/average-statistics', [DashboardController::class, 'averageStatistics']);
        Route::get('/perbandingan-periode', [DashboardController::class, 'perbandinganPeriode']);
        Route::get('/jam-sibuk', [DashboardController::class, 'jamSibukKunjungan']);
    });


    Route::apiResource('/tamu', \App\Http\Controllers\Api\Admin\TamuController::class);
    Route::put('/tamu/{tamu}/status-tamu', [\App\Http\Controllers\Api\Admin\TamuController::class, 'updateStatusTamu']);
    Route::get('/tamu/export/excel', [\App\Http\Controllers\Api\Admin\TamuController::class, 'export']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/all', [PermissionController::class, 'all']);

    Route::get('/kategori-kunjungan/all', [KategoriKunjunganController::class, 'all']);
    Route::apiResource('/kategori-kunjungan', KategoriKunjunganController::class);
    Route::get('/roles/all', [\App\Http\Controllers\Api\Admin\RoleController::class, 'all']);

    Route::apiResource('/roles', RoleController::class);

    Route::get('/users/role/pic', [UserController::class, 'getPicUsers']);
    Route::apiResource('/users', \App\Http\Controllers\Api\Admin\UserController::class);

    Route::apiResource('/penanggung-jawab', \App\Http\Controllers\Api\Admin\PenanggungJawabController::class);
});




Route::prefix('public')->group(function () {
    Route::post('/tamu', [\App\Http\Controllers\Api\Public\TamuController::class, 'store']);
    Route::get('/penilaian/{kode_kunjungan}', [\App\Http\Controllers\Api\Public\PenilaianController::class, 'show']);
    Route::post('/penilaian/{kode_kunjungan}', [\App\Http\Controllers\Api\Public\PenilaianController::class, 'store']);
    Route::get('/kategori-kunjungan/all', [\App\Http\Controllers\Api\Public\KategoriKunjunganController::class, 'all']);
});
