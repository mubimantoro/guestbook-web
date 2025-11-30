<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $apiUrl;
    protected $token;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.fonnte.api_url');
        $this->token = config('services.fonnte.token');
    }

    public function sendNotificationToAdmin($tamu)
    {
        try {
            $admins = User::role('admin')->get();

            $message = $this->buildAdminNotificationMessage($tamu);

            foreach ($admins as $admin) {
                if (!empty($admin->nomor_hp)) {
                    $phoneNumber = $this->formatPhoneNumber($admin->nomor_hp);

                    if ($this->sendMessage($phoneNumber, $message)) {
                        Log::info("Notification sent to admin: {$admin->nama_lengkap} ({$phoneNumber})");
                    } else {
                        Log::error("Failed to send notification to admin: {$admin->nama_lengkap}");
                    }
                } else {
                    Log::warning("Admin {$admin->nama_lengkap} has no phone number");
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sendNotificationToAdmin: ' . $e->getMessage());
            return false;
        }
    }


    public function sendNotificationToPIC($penanggungJawab, $tamu)
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($penanggungJawab->user->nomor_hp);
            $message = $this->buildPICNotificationMessage($penanggungJawab, $tamu);
            return $this->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to sendNotificationtoPIC: ' . $e->getMessage());
            return false;
        }
    }

    public function sendFeedbackNotification($tamu)
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($tamu->nomor_hp);
            $message = $this->buildFeedbackMessage($tamu);

            return $this->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to sendFeedbackNotification: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildAdminNotificationMessage($tamu)
    {
        $tanggalKunjungan = \Carbon\Carbon::parse($tamu->tanggal_kunjungan)
            ->locale('id')
            ->translatedFormat('l, j F Y');

        return "🔔 *TAMU BARU TERDAFTAR*\n\n"
            . "Halo Admin,\n\n"
            . "Ada pendaftaran tamu baru yang perlu diproses:\n\n"
            . "*Detail Tamu:*\n"
            . "Nama: {$tamu->nama_lengkap}\n"
            . "Instansi: {$tamu->instansi}\n"
            . "No. HP: {$tamu->nomor_hp}\n"
            . "Tujuan Kunjungan: {$tamu->kategoriKunjungan->nama}\n"
            . "Tanggal Kunjungan: {$tanggalKunjungan}\n"
            . ($tamu->catatan ? "Keperluan: {$tamu->catatan}\n" : "")
            . "\nStatus Tamu: *{$tamu->status}*\n\n"
            . "Silakan login ke sistem untuk memproses pendaftaran ini.";
    }

    protected function buildFeedbackMessage($tamu)
    {
        $link = config('app.frontend_url') . "/penilaian/{$tamu->kode_kunjungan}";
        $waktuBertemu = \Carbon\Carbon::parse($tamu->waktu_temu)
            ->locale('id')
            ->translatedFormat('l, j F Y');


        return "✅ *PERTEMUAN SELESAI*\n\n"
            . "Halo *{$tamu->nama_lengkap}*,\n\n"
            . "Terima kasih telah berkunjung!\n\n"
            . "*Detail Pertemuan:*\n"
            . "Waktu: {$waktuBertemu}\n"
            . "PIC: {$tamu->pic->user->nama_lengkap}\n"
            . "*Berikan Penilaian Anda*\n"
            . "Kami sangat menghargai feedback Anda untuk meningkatkan pelayanan kami.\n\n"
            . "Klik link berikut untuk memberikan penilaian:\n"
            . "{$link}\n\n"
            . "_Link ini bersifat personal dan hanya dapat digunakan sekali._";
    }

    public function sendMessage($phoneNumber, $message)
    {

        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])->post($this->apiUrl, [
                    'target' => $phoneNumber,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] == true) {
                    Log::info("WhatsApp message sent successfully to {$phoneNumber}");
                    return true;
                }
            }

            Log::warning("WhatsApp message failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Fonnte API Error: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildPICNotificationMessage($penanggungJawab, $tamu)
    {
        $tanggalKunjungan = \Carbon\Carbon::parse($tamu->tanggal_kunjungan)
            ->locale('id')
            ->translatedFormat('l, j F Y');

        return "🔔 *NOTIFIKASI TAMU BARU*\n\n"
            . "Halo *{$penanggungJawab->user->nama_lengkap}*,\n\n"
            . "Ada tamu baru yang perlu Anda konfirmasi:\n\n"
            . "*Detail Tamu:*\n"
            . "Nama: {$tamu->nama_lengkap}\n"
            . "Instansi: {$tamu->instansi}\n"
            . "No. HP: {$tamu->nomor_hp}\n"
            . "Tujuan Kunjungan: {$tamu->kategoriKunjungan->nama}\n"
            . "Tanggal Kunjungan: {$tanggalKunjungan}\n"
            . ($tamu->catatan ? "Catatan: {$tamu->catatan}\n" : "")
            . "\nStatus Tamu: *{$tamu->status}*\n\n"
            . "Silakan login ke sistem untuk melakukan konfirmasi.";
    }

    protected function formatPhoneNumber($phoneNumber)
    {

        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }

        if (substr($phoneNumber, 0, 2) !== '62') {
            $phoneNumber = '62' . $phoneNumber;
        }

        return $phoneNumber;
    }
}
