<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TamuResource;
use App\Models\Tamu;
use Illuminate\Http\Request;
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
        $tamu = Tamu::with('kategoriKunjungan')->whereId($id)->first();

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
}
