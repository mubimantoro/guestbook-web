<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenanggungJawabResource;
use App\Models\Absensi;
use App\Models\PenanggungJawab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenanggungJawabController extends Controller
{
    public function getAvailableStaff(Request $request)
    {
        try {
            $request->validate([
                'kategori_kunjungan_id' => 'required|exists:kategori_kunjungans,id',
                'tanggal' => 'required|date'
            ]);

            $kategoriId = $request->kategori_kunjungan_id;
            $tanggal = $request->tanggal;

            $availableStaff = PenanggungJawab::with(['user', 'kategoriKunjungan'])
                ->where('kategori_kunjungan_id', $kategoriId)
                ->whereHas('absensi', function ($query) use ($tanggal) {
                    $query->where('tanggal', $tanggal)
                        ->where('status', 'hadir');
                })
                ->get();

            return new PenanggungJawabResource(true, 'Data staff tersedia', $availableStaff);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data staff tersedia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
