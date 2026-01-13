<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriKunjungan;
use App\Models\PenanggungJawab;
use App\Models\Tamu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $statistics = [
                // Total keseluruhan
                'total_tamu' => Tamu::count(),
                'total_kategori' => KategoriKunjungan::count(),
                'total_pic' => PenanggungJawab::count(),

                // Statistik berdasarkan status
                'status_summary' => [
                    'pending' => Tamu::where('status', 'Menunggu Konfirmasi')->count(),
                    'approved' => Tamu::where('status', 'Disetujui')->count(),
                    'not_met' => Tamu::where('status', 'Tidak Bertemu')->count(),
                    'completed' => Tamu::where('status', 'Selesai')->count(),
                ],
                // Tamu hari ini
                'tamu_hari_ini' => Tamu::whereDate('tanggal_kunjungan', Carbon::today())->count(),

                // Tamu minggu ini
                'tamu_minggu_ini' => Tamu::whereBetween('tanggal_kunjungan', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),

                // Tamu bulan ini
                'tamu_bulan_ini' => Tamu::whereMonth('tanggal_kunjungan', Carbon::now()->month)
                    ->whereYear('tanggal_kunjungan', Carbon::now()->year)
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik dashboard berhasil diambil',
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function topStaffByVisitors(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);

            $topStaff = PenanggungJawab::select(
                'penanggung_jawabs.id',
                'penanggung_jawabs.user_id',
                DB::raw('COUNT(tamus.id) as total_tamu')
            )
                ->join('users', 'penanggung_jawabs.user_id', '=', 'users.id')
                ->leftJoin('tamus', 'penanggung_jawabs.id', '=', 'tamus.penanggung_jawab_id')
                ->groupBy('penanggung_jawabs.id', 'penanggung_jawabs.user_id')
                ->orderBy('total_tamu', 'desc')
                ->limit($limit)
                ->with(['user:id,nama_lengkap,email', 'kategoriKunjungan:id,nama_kategori'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_staff' => $item->user->nama_lengkap,
                        'email' => $item->user->email,
                        'kategori' => $item->kategoriKunjungan->nama_kategori ?? 'N/A',
                        'total_tamu' => $item->total_tamu,
                        'initial' => strtoupper(substr($item->user->nama_lengkap, 0, 1))
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data staff penerima tamu terbanyak berhasil diambil',
                'data' => $topStaff
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grafik kunjungan per kategori
     */
    public function kunjunganPerKategori()
    {
        try {
            $data = Tamu::select('kategori_kunjungan_id', DB::raw('count(*) as total'))
                ->with('kategoriKunjungan:id,nama')
                ->groupBy('kategori_kunjungan_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'kategori_id' => $item->kategori_kunjungan_id,
                        'kategori_nama' => $item->kategoriKunjungan->nama,
                        'total' => $item->total,
                        'percentage' => 0 // akan dihitung setelah mapping
                    ];
                });

            // Hitung persentase
            $totalAll = $data->sum('total');
            $data = $data->map(function ($item) use ($totalAll) {
                $item['percentage'] = $totalAll > 0
                    ? round(($item['total'] / $totalAll) * 100, 2)
                    : 0;
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data kunjungan per kategori berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan per kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grafik kunjungan per instansi (Top 10)
     */
    public function kunjunganPerInstansi()
    {
        try {
            $data = Tamu::select('instansi', DB::raw('count(*) as total'))
                ->groupBy('instansi')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'instansi' => $item->instansi,
                        'total' => $item->total
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data kunjungan per instansi berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan per instansi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trend kunjungan per bulan (12 bulan terakhir)
     */
    public function trendKunjunganBulanan()
    {
        try {
            $data = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $count = Tamu::whereMonth('tanggal_kunjungan', $date->month)
                    ->whereYear('tanggal_kunjungan', $date->year)
                    ->count();

                $data[] = [
                    'bulan' => $date->format('M Y'),
                    'bulan_angka' => $date->format('m'),
                    'tahun' => $date->format('Y'),
                    'total' => $count
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Trend kunjungan bulanan berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil trend kunjungan bulanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trend kunjungan per minggu (8 minggu terakhir)
     */
    public function trendKunjunganMingguan()
    {
        try {
            $data = [];

            for ($i = 7; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

                $count = Tamu::whereBetween('tanggal_kunjungan', [$startOfWeek, $endOfWeek])
                    ->count();

                $data[] = [
                    'minggu' => 'Minggu ' . $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M'),
                    'start_date' => $startOfWeek->format('Y-m-d'),
                    'end_date' => $endOfWeek->format('Y-m-d'),
                    'total' => $count
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Trend kunjungan mingguan berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil trend kunjungan mingguan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get distribusi status kunjungan
     */
    public function distribusiStatus()
    {
        try {
            $data = Tamu::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'total' => $item->total,
                        'color' => $this->getStatusColor($item->status)
                    ];
                });

            // Hitung persentase
            $totalAll = $data->sum('total');
            $data = $data->map(function ($item) use ($totalAll) {
                $item['percentage'] = $totalAll > 0
                    ? round(($item['total'] / $totalAll) * 100, 2)
                    : 0;
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Distribusi status berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil distribusi status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kunjungan per PIC
     */
    public function kunjunganPerPIC()
    {
        try {
            $data = Tamu::select('pic_id', DB::raw('count(*) as total'))
                ->whereNotNull('pic_id')
                ->with('pic.user:id,nama_lengkap')
                ->groupBy('pic_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'pic_id' => $item->pic_id,
                        'pic_nama' => $item->pic->user->nama_lengkap ?? 'Unknown',
                        'total' => $item->total
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data kunjungan per PIC berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan per PIC',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent visitors (10 terbaru)
     */
    public function recentVisitors()
    {
        try {
            $data = Tamu::with(['kategoriKunjungan:id,nama', 'pic.user:id,nama_lengkap'])
                ->latest('tanggal_kunjungan')
                ->limit(10)
                ->get()
                ->map(function ($tamu) {
                    return [
                        'id' => $tamu->id,
                        'kode_kunjungan' => $tamu->kode_kunjungan,
                        'nama_lengkap' => $tamu->nama_lengkap,
                        'instansi' => $tamu->instansi,
                        'kategori' => $tamu->kategoriKunjungan->nama,
                        'status' => $tamu->status,
                        'tanggal_kunjungan' => $tamu->tanggal_kunjungan?->format('Y-m-d H:i:s'),
                        'pic_nama' => $tamu->pic->user->nama_lengkap ?? null
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data pengunjung terbaru berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengunjung terbaru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistik rata-rata kunjungan
     */
    public function averageStatistics()
    {
        try {
            // Rata-rata kunjungan per hari dalam 30 hari terakhir
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $totalKunjungan30Hari = Tamu::where('tanggal_kunjungan', '>=', $thirtyDaysAgo)->count();
            $rataRataPerHari = round($totalKunjungan30Hari / 30, 2);

            // Rata-rata kunjungan per minggu dalam 3 bulan terakhir
            $threeMonthsAgo = Carbon::now()->subMonths(3);
            $totalKunjungan3Bulan = Tamu::where('tanggal_kunjungan', '>=', $threeMonthsAgo)->count();
            $jumlahMinggu = ceil($threeMonthsAgo->diffInDays(Carbon::now()) / 7);
            $rataRataPerMinggu = round($totalKunjungan3Bulan / $jumlahMinggu, 2);

            // Rata-rata kunjungan per bulan dalam 1 tahun terakhir
            $oneYearAgo = Carbon::now()->subYear();
            $totalKunjungan1Tahun = Tamu::where('tanggal_kunjungan', '>=', $oneYearAgo)->count();
            $rataRataPerBulan = round($totalKunjungan1Tahun / 12, 2);

            $data = [
                'rata_rata_per_hari' => $rataRataPerHari,
                'rata_rata_per_minggu' => $rataRataPerMinggu,
                'rata_rata_per_bulan' => $rataRataPerBulan,
                'periode' => [
                    'per_hari' => '30 hari terakhir',
                    'per_minggu' => '3 bulan terakhir',
                    'per_bulan' => '1 tahun terakhir'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik rata-rata berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik rata-rata',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get perbandingan periode (bulan ini vs bulan lalu)
     */
    public function perbandinganPeriode()
    {
        try {
            // Bulan ini
            $bulanIni = Tamu::whereMonth('tanggal_kunjungan', Carbon::now()->month)
                ->whereYear('tanggal_kunjungan', Carbon::now()->year)
                ->count();

            // Bulan lalu
            $bulanLalu = Tamu::whereMonth('tanggal_kunjungan', Carbon::now()->subMonth()->month)
                ->whereYear('tanggal_kunjungan', Carbon::now()->subMonth()->year)
                ->count();

            // Hitung persentase perubahan
            $perubahan = 0;
            if ($bulanLalu > 0) {
                $perubahan = round((($bulanIni - $bulanLalu) / $bulanLalu) * 100, 2);
            }

            $data = [
                'bulan_ini' => [
                    'total' => $bulanIni,
                    'label' => Carbon::now()->format('F Y')
                ],
                'bulan_lalu' => [
                    'total' => $bulanLalu,
                    'label' => Carbon::now()->subMonth()->format('F Y')
                ],
                'perubahan' => [
                    'nilai' => $perubahan,
                    'status' => $perubahan > 0 ? 'naik' : ($perubahan < 0 ? 'turun' : 'stabil'),
                    'text' => abs($perubahan) . '% ' . ($perubahan > 0 ? 'lebih tinggi' : ($perubahan < 0 ? 'lebih rendah' : 'sama'))
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Perbandingan periode berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil perbandingan periode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get jam sibuk kunjungan
     */
    public function jamSibukKunjungan()
    {
        try {
            $data = Tamu::select(
                DB::raw('HOUR(tanggal_kunjungan) as jam'),
                DB::raw('count(*) as total')
            )
                ->whereNotNull('tanggal_kunjungan')
                ->groupBy('jam')
                ->orderBy('jam')
                ->get()
                ->map(function ($item) {
                    return [
                        'jam' => str_pad($item->jam, 2, '0', STR_PAD_LEFT) . ':00',
                        'total' => $item->total
                    ];
                });

            // Fill missing hours with 0
            $allHours = collect(range(0, 23))->map(function ($hour) use ($data) {
                $jamStr = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                $existing = $data->firstWhere('jam', $jamStr);

                return [
                    'jam' => $jamStr,
                    'total' => $existing ? $existing['total'] : 0
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data jam sibuk kunjungan berhasil diambil',
                'data' => $allHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jam sibuk kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function untuk warna status
     */
    private function getStatusColor($status): string
    {
        return match ($status) {
            'Menunggu Konfirmasi' => '#FFA500',
            'Disetujui' => '#4CAF50',
            'Tidak Bertemu' => '#F44336',
            'Selesai' => '#2196F3',
            'Dibatalkan' => '#9E9E9E',
            default => '#757575'
        };
    }
}
