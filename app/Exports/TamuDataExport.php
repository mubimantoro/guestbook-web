<?php

namespace App\Exports;

use App\Models\Tamu;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TamuDataExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Tamu::query()
            ->with(['kategoriKunjungan', 'penanggungJawab.user', 'riwayatRescheduleKunjungan'])
            ->select([
                'id',
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
                'reschedule_count',
                'created_at'
            ]);

        // Apply filters
        if (!empty($this->filters['tanggal_dari']) && !empty($this->filters['tanggal_sampai'])) {
            $query->whereBetween('tanggal_kunjungan', [
                $this->filters['tanggal_dari'] . ' 00:00:00',
                $this->filters['tanggal_sampai'] . ' 23:59:59'
            ]);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['kategori_kunjungan_id'])) {
            $query->where('kategori_kunjungan_id', $this->filters['kategori_kunjungan_id']);
        }

        if (!empty($this->filters['penanggung_jawab_id'])) {
            $query->where('penanggung_jawab_id', $this->filters['penanggung_jawab_id']);
        }

        return $query->latest('tanggal_kunjungan');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Kunjungan',
            'Nama Lengkap',
            'Nomor HP',
            'Instansi',
            'Kategori Kunjungan',
            'Penanggung Jawab',
            'Tanggal Kunjungan',
            'Status',
            'Waktu Temu',
            'Alasan Batal',
            'Dijadwalkan Ulang',
            'Jumlah Reschedule',
            'Catatan',
            'Tanggal Daftar'
        ];
    }

    public function map($tamu): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $tamu->kode_kunjungan ?? '-',
            $tamu->nama_lengkap ?? '-',
            $tamu->nomor_hp ?? '-',
            $tamu->instansi ?? '-',
            $tamu->kategoriKunjungan->nama ?? '-',
            $tamu->penanggungJawab && $tamu->penanggungJawab->user
                ? $tamu->penanggungJawab->user->nama_lengkap
                : '-',
            $tamu->tanggal_kunjungan
                ? Carbon::parse($tamu->tanggal_kunjungan)->locale('id')->translatedFormat('j F Y, H:i')
                : '-',
            $this->formatStatus($tamu->status),
            $tamu->waktu_temu
                ? Carbon::parse($tamu->waktu_temu)->locale('id')->translatedFormat('j F Y, H:i')
                : '-',
            $tamu->alasan_batal ?? '-',
            $tamu->is_rescheduled ? 'Ya' : 'Tidak',
            $tamu->reschedule_count ?? 0,
            $tamu->catatan ?? '-',
            $tamu->created_at
                ? Carbon::parse($tamu->created_at)->locale('id')->translatedFormat('j F Y, H:i')
                : '-',
        ];
    }

    public function formatStatus($status)
    {
        $statusMap = [
            'Pending' => 'Menunggu',
            'Disetujui' => 'Bertemu',
            'Tidak Bertemu' => 'Tidak Bertemu',
            'Dijadwalkan Ulang' => 'Dijadwalkan Ulang',
            'Dibatalkan' => 'Dibatalkan',
        ];

        return $statusMap[$status] ?? $status;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Pengunjung';
    }
}
