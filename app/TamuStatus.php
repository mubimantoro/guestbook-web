<?php

namespace App;

enum TamuStatus: string
{
    case Pending = 'Menunggu Konfirmasi';
    case Approved = 'Disetujui';
    case NotMet = 'Tidak Bertemu';
    case Completed = 'Selesai';
    case Rescheduled = 'Dijadwalkan Ulang';
    case Cancelled = 'Dibatalkan';
}
