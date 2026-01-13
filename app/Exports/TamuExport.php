<?php

namespace App\Exports;

use App\Models\Tamu;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TamuExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Tamu::query()
            ->with(['kategoriKunjungan', 'penanggungJawab.user', 'penilaian'])
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
                'created_at'
            ])
            ->latest('tanggal_kunjungan');
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
            'Catatan',
            'Indeks Kepuasan',
            'Keterangan',
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
            $tamu->pic && $tamu->pic->user ? $tamu->pic->user->nama_lengkap : '-',
            $tamu->tanggal_kunjungan ? Carbon::parse($tamu->tanggal_kunjungan)->locale('id')->translatedFormat('j F Y') : '-',
            $this->formatStatus($tamu->status),
            $tamu->waktu_temu ? Carbon::parse($tamu->waktu_temu)->locale('id')->translatedFormat('j F Y') : '-',
            $tamu->alasan_batal ?? '-',
            $tamu->catatan ?? '-',
            $tamu->penilaian ? $tamu->penilaian->rating : '-',
            $tamu->penilaian ? $tamu->penilaian->komentar : '-',
            $tamu->created_at ? Carbon::parse($tamu->created_at)->locale('id')->translatedFormat('j F Y, H:i') : '-',
        ];
    }

    public function formatStatus($status)
    {
        $statusMap = [
            'pending' => 'Menunggu Konfirmasi',
            'approved' => 'Disetujui',
            'not_met' => 'Tidak Bertemu',
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
}
