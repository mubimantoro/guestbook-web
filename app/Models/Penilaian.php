<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $fillable = [
        'tamu_id',
        'rating',
        'keterangan'
    ];

    public function tamu()
    {
        return $this->belongsTo(Tamu::class, 'tamu_id');
    }
}
