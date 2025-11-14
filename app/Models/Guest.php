<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'nama',
        'nomor_hp',
        'institusi',
        'tujuan',
        'tanggal_kunjungan',
        'status',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime'
    ];
}
