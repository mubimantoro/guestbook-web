<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'penanggung_jawab_id',
        'tanggal',
        'status'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];


    public function penanggungJawab()
    {
        return $this->belongsTo(PenanggungJawab::class);
    }
}
