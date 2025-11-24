<?php

namespace App\Services;

use GuzzleHttp\Client;
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

    public function sendMessage($phone, $message)
    {
        try {
            $phone = $this->formatPhoneNumber($phone);

            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->token,
                ],
                'form_params' => [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('WhatsApp sent successfully', [
                'phone' => $phone,
                'response' => $result
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp sent successfully',
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp: ' . $e->getMessage()
            ];
        }
    }

    protected function formatPhoneNumber($phone)
    {

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }


        if (substr($phone, 0, 2) != '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    public function notifyAdminNewGuest($guest)
    {
        $adminPhone = config('services.fonnte.admin_phone');

        $message = $this->formatNewGuestMessage($guest);

        return $this->sendMessage($adminPhone, $message);
    }

    public function sendGuestConfirmation($guest)
    {
        $message = $this->formatGuestConfirmationMessage($guest);

        return $this->sendMessage($guest['nomor_hp'], $message);
    }

    protected function formatNewGuestMessage($guest)
    {
        $tanggal = $guest['tanggal_kunjungan']
            ? date('d/m/Y H:i', strtotime($guest['tanggal_kunjungan']))
            : 'Tidak ditentukan';

        return "🔔 *TAMU BARU TERDAFTAR*\n\n" .
            " *Nama:* {$guest['nama_lengkap']}\n" .
            "*No HP:* {$guest['nomor_hp']}\n" .
            "*Instansi:* {$guest['instansi']}\n" .
            // "*Tujuan:* {$guest['tujuan']}\n" .
            "*Tanggal Kunjungan:* {$tanggal}\n" .
            ($guest['catatan'] ? "📌 *Catatan:* {$guest['catatan']}\n\n" : "") .
            "Silakan cek dashboard untuk detail lengkap.";
    }

    protected function formatGuestConfirmationMessage($guest)
    {
        $tanggal = $guest['tanggal_kunjungan']
            ? date('d/m/Y H:i', strtotime($guest['tanggal_kunjungan']))
            : 'Akan dikonfirmasi';

        return "✅ *TERIMA KASIH TELAH MENDAFTAR*\n\n" .
            "Halo *{$guest['nama_lengkap']}*,\n\n" .
            "Pendaftaran Anda telah kami terima dengan detail:\n\n" .
            "*Instansi:* {$guest['instansi']}\n" .
            // "*Tujuan:* {$guest['tujuan']}\n" .
            "*Tanggal:* {$tanggal}\n\n" .
            "Tim kami akan segera menghubungi Anda untuk konfirmasi.\n\n" .
            "Terima kasih! 🙏";
    }

    public function notifyStatusChange($guest, $oldStatus, $newStatus)
    {
        $statusMessages = [
            'approved' => "Kunjungan Anda telah *DISETUJUI*",
            'cancelled' => "Kunjungan Anda *DIBATALKAN*",
        ];

        $statusMessage = $statusMessages[$newStatus] ?? "Status berubah menjadi: " . strtoupper($newStatus);

        $message = "🔔 *UPDATE STATUS KUNJUNGAN*\n\n" .
            "Halo *{$guest['nama_lengkap']}*,\n\n" .
            "{$statusMessage}\n\n" .
            "*Detail Kunjungan:*\n" .
            "Instansi: {$guest['instansi']}\n" .
            // "Tujuan: {$guest['tujuan']}\n\n" .
            "Hubungi kami jika ada pertanyaan.\n\n" .
            "Terima kasih! 🙏";

        return $this->sendMessage($guest['nomor_hp'], $message);
    }
}
