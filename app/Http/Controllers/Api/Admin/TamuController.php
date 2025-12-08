<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\StatusTamuUpdated;
use App\Exports\TamuExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\TamuResource;
use App\Models\Tamu;
use App\TamuStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TamuController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware(['permission:tamu'], only: ['index', 'show', 'update', 'updateStatusTamu'])
        ];
    }

    public function index(Request $request)
    {
        $tamu = Tamu::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('instansi', 'like', '%' . $search . '%')
                    ->orWhere('kode_kunjungan', 'like', '%' . $search . '%');
            })
            ->when($request->tanggal_dari && $request->tanggal_sampai, function ($query) use ($request) {
                $query->whereBetween('tanggal_kunjungan', [
                    $request->tanggal_dari . ' 00:00:00',
                    $request->tanggal_sampai . ' 23:59:59'
                ]);
            })
            ->when($request->tanggal_dari && !$request->tanggal_sampai, function ($query) use ($request) {
                $query->whereDate('tanggal_kunjungan', '>=', $request->tanggal_dari);
            })
            ->when(!$request->tanggal_dari && $request->tanggal_sampai, function ($query) use ($request) {
                $query->whereDate('tanggal_kunjungan', '<=', $request->tanggal_sampai);
            })
            ->with('kategoriKunjungan')
            ->latest()
            ->paginate(5);


        $tamu->appends(['search' => request()->search]);

        return new TamuResource(true, 'List data Tamu', $tamu);
    }

    public function show($id)
    {
        $tamu = Tamu::with([
            'kategoriKunjungan',
            'pic.user',
            'penilaian'
        ])->whereId($id)->first();

        if ($tamu) {
            return new TamuResource(true, 'Detail Data Tamu', $tamu);
        }
        return new TamuResource(false, 'Detail Data Tamu tidak ditemukan', null);
    }

    public function update(Request $request, Tamu $tamu)
    {

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string',
            'nomor_hp' => 'sometimes|string|max:15',
            'instansi' => 'sometimes|string',
            'tanggal_kunjungan' => 'nullable|date',
            'kategori_kunjungan_id' => 'sometimes|exists:kategori_kunjungans,id',
            'pic_id' => 'nullable|exists:penanggung_jawabs,id',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tamu->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_hp' => $request->nomor_hp,
            'instansi' => $request->instansi,
            'tanggal_kunjungan' => $request->tanggal_kunjungan,
            'kategori_kunjungan_id' => $request->kategori_kunjungan_id,
            'pic_id' => $request->pic_id,
            'catatan' => $request->catatan
        ]);

        if ($tamu) {
            return new TamuResource(true, 'Data Tamu berhasil diupdate', $tamu);
        }

        return new TamuResource(false, 'Data Tamu gagal diupdate', null);
    }

    public function updateStatusTamu(Request $request, Tamu $tamu)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'waktu_temu' => 'required_if:status,' . TamuStatus::Approved->value . '|nullable|date',
            'alasan_batal' => 'required_if:status,' . TamuStatus::NotMet->value . '|nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $updateData = [
                'status' => $request->status,
            ];

            $shouldTriggerEvent = false;
            $isMeet = false;
            $notMeetReason = null;

            if ($request->status ===  TamuStatus::Approved->value) {
                $updateData['waktu_temu'] = $request->waktu_temu;
                $updateData['alasan_batal'] = null;

                $shouldTriggerEvent = true;
                $isMeet = true;
            } else if ($request->status === TamuStatus::NotMet->value) {
                $updateData['waktu_temu'] = null;
                $updateData['alasan_batal'] = $request->alasan_batal;

                $shouldTriggerEvent = true;
                $isMeet = false;
                $notMeetReason = $request->alasan_batal;
            }

            $tamu->update($updateData);
            $tamu->refresh();
            $tamu->load(['kategoriKunjungan', 'pic.user']);

            if ($shouldTriggerEvent) {
                event(new StatusTamuUpdated($tamu, $isMeet, $notMeetReason));

                Log::info("StatusPertemuanUpdated event triggered", [
                    'tamu_id' => $tamu->id,
                ]);
            }

            return new TamuResource(true, 'Status Tamu berhasil diupdate', $tamu);
        } catch (\Exception $e) {
            return new TamuResource(false, 'Gagal mengupdate status Tamu', null);
        }
    }

    public function export(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tanggal_dari' => 'nullable|date',
                'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
                'status' => 'nullable|string',
                'kategori_kunjungan_id' => 'nullable|exists:kategori_kunjungans,id',
                'pic_id' => 'nullable|exists:penanggung_jawabs,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $filters = $request->only([
                'tanggal_dari',
                'tanggal_sampai',
                'status',
                'kategori_kunjungan_id',
                'pic_id'
            ]);

            $timestamp = Carbon::now()->format('Ymd_His');
            $fileName = "tamu_export_{$timestamp}.xlsx";

            return Excel::download(new TamuExport($filters), $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan export data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
