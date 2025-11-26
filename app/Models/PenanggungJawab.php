<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenanggungJawab extends Model
{
    protected $fillable = [
        'user_id',
        'kategori_kunjungan_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tamu()
    {
        return $this->hasMany(Tamu::class, 'pic_id');
    }

    public function kategoriKunjungan()
    {
        return $this->belongsTo(KategoriKunjungan::class);
    }
}
