<?php

namespace App\Services;

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

    public function sendNotificationToPIC($penanggungJawab, $tamu)
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($penanggungJawab->user->nomor_hp);
            $message = $this->buildPICNotificationMessage($penanggungJawab, $tamu);
            return $this->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification to PIC: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPenilaianLink($tamu)
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($tamu->nomor_hp);
            $message = $this->buildPenilaianLinkMessage($tamu);

            return $this->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send penilaian link: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildPenilaianLinkMessage($tamu)
    {
        $link = url("/penilaian/{$tamu->kode_kunjungan}");
        $waktuBertemu = \Carbon\Carbon::parse($tamu->waktu_bertemu)->format('d/m/Y H:i');

        return "✅ *PERTEMUAN SELESAI*\n\n"
            . "Halo *{$tamu->nama_lengkap}*,\n\n"
            . "Terima kasih telah berkunjung!\n\n"
            . "📋 *Detail Pertemuan:*\n"
            . "Waktu: {$waktuBertemu}\n"
            . "PIC: {$tamu->pic->user->nama_lengkap}\n\n"
            . "⭐ *Berikan Penilaian Anda*\n"
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

    public function sendConfirmationToGuest($tamu)
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($tamu->nomor_hp);
            $message = $this->buildGuestConfirmationMessage($tamu);

            return $this->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp confirmation to guest: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildGuestConfirmationMessage($tamu)
    {
        $tanggalKunjungan = \Carbon\Carbon::parse($tamu->tanggal_kunjungan)->format('d/m/Y H:i');

        return "✅ *PENDAFTARAN BERHASIL*\n\n"
            . "Halo *{$tamu->nama_lengkap}*,\n\n"
            . "Terima kasih telah mendaftar sebagai tamu.\n\n"
            . "*Detail Tamu:*\n"
            . "Instansi: {$tamu->instansi}\n"
            . "Tujuan Kunjungan: {$tamu->kategoriKunjungan->nama}\n"
            . "Tanggal Kunjungan: {$tanggalKunjungan}\n"
            . ($tamu->catatan ? "Keperluan: {$tamu->catatan}\n" : "")
            . "Kami akan segera menghubungi Anda untuk konfirmasi kunjungan.\n\n"
            . "_Terima kasih atas kesabaran Anda._";
    }

    protected function buildPICNotificationMessage($penanggungJawab, $tamu)
    {
        $tanggalKunjungan = \Carbon\Carbon::parse($tamu->tanggal_kunjungan)->format('d/m/Y H:i');

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
            . "\nStatus: ⏳ *{$tamu->status}*\n\n"
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
