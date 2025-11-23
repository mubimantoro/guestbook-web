<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    protected $fillable = [
        'nama',
        'nomor_hp',
        'instansi',
        'kategori_kunjungan',
        'tanggal_kunjungan',
        'catatan',
        'status',
    ];

    public function kategoriKunjungan()
    {
        return $this->belongsTo(KategoriKunjungan::class);
    }
}
