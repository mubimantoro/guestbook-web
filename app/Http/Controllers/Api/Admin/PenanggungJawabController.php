<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenanggungJawabResource;
use App\Models\PenanggungJawab;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PenanggungJawabController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware(['permission:staff'], only: ['index', 'store', 'show', 'update', 'update', 'destroy'])
        ];
    }

    public function index()
    {
        $penanggungJawab = PenanggungJawab::with(['user', 'kategoriKunjungan'])->latest()->paginate(5);

        return new PenanggungJawabResource(true, 'List data Penanggung Jawab', $penanggungJawab);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'kategori_kunjungan_id' => 'required|exists:kategori_kunjungans,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $exists = PenanggungJawab::where('user_id', $request->user_id)
                ->where('kategori_kunjungan_id', $request->kategori_kunjungan_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff sudah terdaftar untuk kategori ini'
                ], 422);
            }

            $penanggungJawab = PenanggungJawab::create([
                'user_id' => $request->user_id,
                'kategori_kunjungan_id' => $request->kategori_kunjungan_id
            ]);

            $penanggungJawab->load(['user', 'kategoriKunjungan']);
            return new PenanggungJawabResource(true, 'Data Staff berhasil ditambahkan', $penanggungJawab);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $penanggungJawab = PenanggungJawab::with(['user', 'kategoriKunjungan', 'tamu'])->whereId($id)->first();

        if ($penanggungJawab) {
            return new PenanggungJawabResource(true, 'Detail Data Penanggung Jawab', $penanggungJawab);
        }

        return new PenanggungJawabResource(false, 'Detail Data Penanggung Jawab', null);
    }

    public function update(Request $request, PenanggungJawab $penanggungJawab)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'kategori_kunjungan_id' => 'sometimes|exists:kategori_kunjungans,id',
                'is_active' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $penanggungJawab->update([
                'user_id' => $request->user_id,
                'kategori_kunjungan_id' => $request->kategori_kunjungan_id,
                'is_active' => $request->is_active
            ]);

            DB::commit();
            return new PenanggungJawabResource(true, 'Data Penanggung Jawab berhasil diupdate', $penanggungJawab);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating penanggung jawab: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data Penanggung Jawab gagal diupdate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PenanggungJawab $penanggungJawab)
    {
        try {
            DB::beginTransaction();

            if ($penanggungJawab->tamu()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus Penanggung Jawab yang masih memiliki tamu terdaftar.',
                ], 422);
            }

            $penanggungJawab->delete();
            DB::commit();
            return new PenanggungJawabResource(true, 'Data Penanggung Jawab berhasil dihapus', null);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting penanggung jawab: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data Penanggung Jawab gagal dihapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
