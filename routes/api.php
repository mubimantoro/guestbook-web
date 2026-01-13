<?php

use App\Http\Controllers\Api\Admin\AbsensiController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\KategoriKunjunganController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\LoginController;
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
        Route::get('/top-staff', [DashboardController::class, 'topStaffByVisitors']);
    });


    Route::apiResource('/tamu', \App\Http\Controllers\Api\Admin\TamuController::class);
    Route::put('/tamu/{tamu}/status-tamu', [\App\Http\Controllers\Api\Admin\TamuController::class, 'updateStatusTamu']);
    Route::get('/tamu/export/excel', [\App\Http\Controllers\Api\Admin\TamuController::class, 'export']);
    Route::get('/tamu/export/data-pengunjung', [\App\Http\Controllers\Api\Admin\TamuController::class, 'exportDataPengunjung']);
    Route::get('/tamu/export/indeks-kepuasan', [\App\Http\Controllers\Api\Admin\TamuController::class, 'exportIndeksKepuasan']);
    Route::post('/tamu/{tamu}/reschedule', [\App\Http\Controllers\Api\Admin\TamuController::class, 'reschedule']);
    Route::get('/tamu/{tamuId}/reschedule-history', [\App\Http\Controllers\Api\Admin\TamuController::class, 'getRescheduleHistory']);
    Route::put('/reschedule/{rescheduleId}/confirm', [\App\Http\Controllers\Api\Admin\TamuController::class, 'confirmReschedule']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/all', [PermissionController::class, 'all']);

    Route::get('/kategori-kunjungan/all', [KategoriKunjunganController::class, 'all']);
    Route::apiResource('/kategori-kunjungan', KategoriKunjunganController::class);
    Route::get('/roles/all', [\App\Http\Controllers\Api\Admin\RoleController::class, 'all']);

    Route::apiResource('/roles', RoleController::class);
    Route::apiResource('/absensi', AbsensiController::class);
    Route::post('/absensi', [AbsensiController::class, 'store']);
    Route::post('/absensi/bulk', [AbsensiController::class, 'bulkStore']);

    Route::get('/users/role/pic', [UserController::class, 'getPicUsers']);
    Route::get('/users/staff', [UserController::class, 'getStaffUsers']);
    Route::apiResource('/users', \App\Http\Controllers\Api\Admin\UserController::class);

    Route::apiResource('/penanggung-jawab', \App\Http\Controllers\Api\Admin\PenanggungJawabController::class);
});




Route::prefix('public')->group(function () {
    Route::post('/tamu', [\App\Http\Controllers\Api\Public\TamuController::class, 'store']);
    Route::get('/penilaian/{kode_kunjungan}', [\App\Http\Controllers\Api\Public\PenilaianController::class, 'show']);
    Route::post('/penilaian/{kode_kunjungan}', [\App\Http\Controllers\Api\Public\PenilaianController::class, 'store']);
    Route::get('/kategori-kunjungan/all', [\App\Http\Controllers\Api\Public\KategoriKunjunganController::class, 'all']);
    Route::get('/staff-available', [\App\Http\Controllers\Api\Public\PenanggungJawabController::class, 'getAvailableStaff']);
});
