<?php

namespace App;

enum GuestStatus: string
{
    case Pending = 'Menunggu Konfirmasi';
    case Approved = 'Disetujui';
    case Cancelled = 'Dibatalkan';
}
