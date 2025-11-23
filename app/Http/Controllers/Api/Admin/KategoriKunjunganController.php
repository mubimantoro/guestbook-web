<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriKunjunganResource;
use App\Models\KategoriKunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KategoriKunjunganController extends Controller
{
    public function index()
    {
        $kategoriKunjungan = KategoriKunjungan::latest()->paginate(5);
        return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan', $kategoriKunjungan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|unique:kategori_kunjungans'
        ], [
            'nama.required' => 'Nama Kategori Kunjungan wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategoriKunjungan = KategoriKunjungan::create([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama, '-')
        ]);

        if ($kategoriKunjungan) {
            return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan berhasil disimpan', $kategoriKunjungan);
        }

        return new KategoriKunjunganResource(false, 'Data Kategori Kunjungan gagal disimpan', null);
    }

    public function show($id)
    {
        $kategoriKunjungan = KategoriKunjungan::whereId($id)->first();

        if ($kategoriKunjungan) {
            return new KategoriKunjunganResource(true, 'Detail Kategori Kunjungan', $kategoriKunjungan);
        }

        return new KategoriKunjunganResource(false, 'Detail data Kategori Kunjungan tidak ditemukan', null);
    }

    public function update(Request $request, KategoriKunjungan $kategoriKunjungan)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|unique:kategori_kunjungans,nama,' . $kategoriKunjungan->id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategoriKunjungan->update([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama, '-')
        ]);

        if ($kategoriKunjungan) {
            return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan berhasil diupdate', $kategoriKunjungan);
        }

        return new KategoriKunjunganResource(false, 'Data Kategori Kunjungan gagal diupdate', null);
    }

    public function destroy(KategoriKunjungan $kategoriKunjungan)
    {
        if ($kategoriKunjungan->delete()) {
            return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan berhasil dihapus', null);
        }

        return new KategoriKunjunganResource(false, 'Data Kategori Kunjungan gagal dihapus', null);
    }

    public function all()
    {
        $kategoriKunjungan = KategoriKunjungan::latest()->get();

        return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan', $kategoriKunjungan);
    }
}
