<?php

namespace App\Http\Controllers\Api\Public;

use App\Events\TamuRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\TamuResource;
use App\Models\PenanggungJawab;
use App\Models\Tamu;
use App\Services\WhatsAppService;
use App\TamuStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TamuController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string',
                'nomor_hp' => 'required|string|max:15',
                'instansi' => 'required|string',
                'kategori_kunjungan_id' => 'required',
                'penanggung_jawab_id' => 'required|exists:penanggung_jawabs,id',
                'tanggal_kunjungan' => 'date',
                'catatan' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $penanggungJawab = PenanggungJawab::find($request->penanggung_jawab_id);

            if ($penanggungJawab->kategori_kunjungan_id != $request->kategori_kunjungan_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff yang dipilih tidak sesuai dengan kategori kunjungan'

                ], 422);
            }

            $tamu = Tamu::create([
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_hp' => $request->nomor_hp,
                'instansi' => $request->instansi,
                'kategori_kunjungan_id' => $request->kategori_kunjungan_id,
                'penanggung_jawab_id' => $request->penanggung_jawab_id,
                'tanggal_kunjungan' => $request->tanggal_kunjungan,
                'catatan' => $request->catatan,
                'status' => TamuStatus::Pending->value
            ]);

            event(new TamuRegistered($tamu));
            $tamu->load('kategoriKunjungan');

            return new TamuResource(true, 'Pendaftaran berhasil!', $tamu);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data Tamu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
