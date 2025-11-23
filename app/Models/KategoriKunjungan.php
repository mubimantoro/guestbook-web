<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKunjungan extends Model
{
    protected $fillable = [
        'nama',
        'slug'
    ];

    public function Tamu()
    {
        return $this->hasMany(Tamu::class);
    }
}
