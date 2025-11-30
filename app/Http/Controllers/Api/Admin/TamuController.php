<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\StatusTamuUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\TamuResource;
use App\Models\Tamu;
use App\TamuStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TamuController extends Controller
{
    public function index()
    {
        $tamu = Tamu::with('kategoriKunjungan')->latest()->paginate(5);


        return new TamuResource(true, 'List data Tamu', $tamu);
    }

    public function show($id)
    {
        $tamu = Tamu::with([
            'kategoriKunjungan',
            'pic.user',
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
}
