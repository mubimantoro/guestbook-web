<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatRescheduleKunjungan extends Model
{
    protected $fillable = [
        'tamu_id',
        'jadwal_lama',
        'jadwal_baru',
        'alasan_reschedule',
        'reschedule_by',
        'whatsapp_sent'
    ];

    protected $casts = [
        'jadwal_lama' => 'datetime',
        'jadwal_baru' => 'datetime',
        'whatsapp_sent' => 'boolean'
    ];

    public function tamu()
    {
        return $this->belongsTo(Tamu::class);
    }

    public function rescheduledBy()
    {
        return $this->belongsTo(User::class, 'reschedule_by');
    }
}
