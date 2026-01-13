<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PenanggungJawab extends Model
{
    protected $fillable = [
        'user_id',
        'kategori_kunjungan_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tamu()
    {
        return $this->hasMany(Tamu::class, 'penanggung_jawab_id');
    }

    public function kategoriKunjungan()
    {
        return $this->belongsTo(KategoriKunjungan::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
