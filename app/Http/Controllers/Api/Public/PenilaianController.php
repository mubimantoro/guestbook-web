<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenilaianResource;
use App\Models\Penilaian;
use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenilaianController extends Controller
{
    public function show($kode_kunjungan)
    {
        $tamu = Tamu::where('kode_kunjungan', $kode_kunjungan)
            ->with(['kategoriKunjungan', 'pic.user', 'penilaian'])
            ->first();

        return new PenilaianResource(true, 'Data Kunjungan Tamu', $tamu);
    }

    public function store(Request $request, $kode_kunjungan)
    {
        $tamu = Tamu::where('kode_kunjungan', $kode_kunjungan)->first();

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'keterangan' => 'required|string'
        ], [
            'rating.required' => 'Rating wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $penilaian = Penilaian::create([
            'tamu_id' => $tamu->id,
            'rating' => $request->rating,
            'keterangan' => $request->keterangan
        ]);

        return new PenilaianResource(true, 'Terima Kasih atas Penilaian Anda!', $penilaian);
    }
}
