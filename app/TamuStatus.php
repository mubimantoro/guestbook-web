<?php

namespace App;

enum TamuStatus: string
{
    case Pending = 'Menunggu Konfirmasi';
    case Approved = 'Disetujui';
    case Cancelled = 'Dibatalkan';
}
