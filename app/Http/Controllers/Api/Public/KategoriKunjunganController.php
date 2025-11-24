<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriKunjunganResource;
use App\Models\KategoriKunjungan;
use Illuminate\Http\Request;

class KategoriKunjunganController extends Controller
{
    public function all()
    {
        $kategoriKunjungan = KategoriKunjungan::latest()->get();

        return new KategoriKunjunganResource(true, 'Data Kategori Kunjungan', $kategoriKunjungan);
    }
}
