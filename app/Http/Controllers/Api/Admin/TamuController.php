<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TamuResource;
use App\Models\Tamu;
use Illuminate\Http\Request;

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
}
