<?php

namespace App\Exports;

use App\Models\Tamu;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IndeksKepuasanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Tamu::query()
            ->with(['kategoriKunjungan', 'penanggungJawab.user', 'penilaian'])
            ->whereHas('penilaian')
            ->select([
                'id',
                'kode_kunjungan',
                'nama_lengkap',
                'nomor_hp',
                'instansi',
                'kategori_kunjungan_id',
                'penanggung_jawab_id',
                'tanggal_kunjungan',
                'waktu_temu',
                'created_at'
            ]);

        if (!empty($this->filters['tanggal_dari']) && !empty($this->filters['tanggal_sampai'])) {
            $query->whereBetween('tanggal_kunjungan', [
                $this->filters['tanggal_dari'] . ' 00:00:00',
                $this->filters['tanggal_sampai'] . ' 23:59:59'
            ]);
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
            'Nama Pengunjung',
            'Instansi',
            'Kategori Kunjungan',
            'Penanggung Jawab',
            'Tanggal Kunjungan',
            'Tanggal Temu',
            'Rating Kepuasan',
            'Kategori Penilaian',
            'Keterangan/Feedback',
            'Tanggal Penilaian'
        ];
    }

    public function map($tamu): array
    {
        static $no = 0;
        $no++;

        $rating = $tamu->penilaian ? $tamu->penilaian->rating : 0;

        return [
            $no,
            $tamu->kode_kunjungan ?? '-',
            $tamu->nama_lengkap ?? '-',
            $tamu->instansi ?? '-',
            $tamu->kategoriKunjungan->nama ?? '-',
            $tamu->penanggungJawab && $tamu->penanggungJawab->user
                ? $tamu->penanggungJawab->user->nama_lengkap
                : '-',
            $tamu->tanggal_kunjungan
                ? Carbon::parse($tamu->tanggal_kunjungan)->locale('id')->translatedFormat('j F Y')
                : '-',
            $tamu->waktu_temu
                ? Carbon::parse($tamu->waktu_temu)->locale('id')->translatedFormat('j F Y')
                : '-',
            $rating . ' / 5',
            $this->getKategoriPenilaian($rating),
            $tamu->penilaian ? ($tamu->penilaian->keterangan ?? '-') : '-',
            $tamu->penilaian
                ? Carbon::parse($tamu->penilaian->created_at)->locale('id')->translatedFormat('j F Y, H:i')
                : '-',
        ];
    }

    public function getKategoriPenilaian($rating)
    {
        if ($rating >= 4.5) {
            return 'Sangat Baik';
        } elseif ($rating >= 3.5) {
            return 'Baik';
        } elseif ($rating >= 2.5) {
            return 'Cukup';
        } elseif ($rating >= 1.5) {
            return 'Kurang';
        } else {
            return 'Sangat Kurang';
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '00B050']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }

    public function title(): string
    {
        return 'Indeks Kepuasan';
    }
}
