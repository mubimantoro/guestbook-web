<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tamu extends Model
{
    protected $fillable = [
        'kode_kunjungan',
        'nama_lengkap',
        'nomor_hp',
        'instansi',
        'kategori_kunjungan_id',
        'penanggung_jawab_id',
        'tanggal_kunjungan',
        'catatan',
        'status',
        'waktu_temu',
        'alasan_batal',
        'is_rescheduled',
        'reschedule_count'
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
        'waktu_temu' => 'datetime',
        'is_rescheduled' => 'boolean',
        'reschedule_count' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($tamu) {
            $tamu->kode_kunjungan = 'TKGTK-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        });
    }

    public function kategoriKunjungan()
    {
        return $this->belongsTo(KategoriKunjungan::class);
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(PenanggungJawab::class, 'penanggung_jawab_id');
    }

    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'tamu_id');
    }

    public function riwayatRescheduleKunjungan()
    {
        return $this->hasMany(RiwayatRescheduleKunjungan::class)->latest();
    }

    public function latestRescheduleKunjungan()
    {
        return $this->hasOne(RiwayatRescheduleKunjungan::class)->latestOfMany();
    }
}
