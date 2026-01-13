<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbsensiResource;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware(['permission:absensi_staff'], only: ['index', 'store', 'bulkStore'])
        ];
    }

    public function index(Request $request)
    {
        $absensi = Absensi::with(['penanggungJawab.user', 'penanggungJawab.kategoriKunjungan'])->latest()->paginate(5);

        return new AbsensiResource(true, 'List data Absensi', $absensi);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penanggung_jawab_id' => 'required|exists:penanggung_jawabs,id',
            'tanggal' => 'required|date',
            'status' => 'required|'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $absensi = Absensi::updateOrCreate(
                [
                    'penanggung_jawab_id' => $request->penanggung_jawab_id,
                    'tanggal' => $request->tanggal
                ],
                [
                    'status' => $request->status
                ]
            );
            $absensi->load(['penanggungJawab.user', 'penanggungJawab.kategoriKunjungan']);
            $message = $absensi->wasRecentlyCreated ? 'Absensi berhasil ditambahkan' : 'Absensi berhasil diupdate';
            return new AbsensiResource(true, $message, $absensi);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'absensis' => 'required|array',
            'absensis.*.penanggung_jawab_id' => 'required|exists:penanggung_jawabs,id',
            'absensis.*.tanggal' => 'required|date',
            'absensis.*.status' => 'required|'
        ], [
            'absensis.required' => 'Data absensi harus diisi',
            'absensis.array' => 'Format data absensi tidak valid',
            'absensis.*.penanggung_jawab_id.required' => 'Penanggung jawab harus dipilih',
            'absensis.*.penanggung_jawab_id.exists' => 'Penanggung jawab tidak ditemukan',
            'absensis.*.tanggal.required' => 'Tanggal harus diisi',
            'absensis.*.status.required' => 'Status harus dipilih',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created = 0;
            $updated = 0;
            $failed = 0;
            $errors = [];

            foreach ($request->absensis as $absensiData) {
                try {
                    $absensi = Absensi::updateOrCreate(
                        [
                            'penanggung_jawab_id' => $absensiData['penanggung_jawab_id'],
                            'tanggal' => $absensiData['tanggal']
                        ],
                        [
                            'status' => $absensiData['status']
                        ]
                    );

                    if ($absensi->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'penanggung_jawab_id' => $absensiData['penanggung_jawab_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk absensi selesai diproses',
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan bulk absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
