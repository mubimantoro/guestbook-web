<?php

namespace App\Listeners;

use App\Events\TamuRegistered;
use App\Models\PenanggungJawab;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendNotificationToPIC
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = [60, 120, 300];
    public $timeout = 60;

    protected $whatsappService;

    /**
     * Create the event listener.
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle the event.
     */
    public function handle(TamuRegistered $event): void
    {
        $tamu = $event->tamu;
        $pic = PenanggungJawab::where('kategori_kunjungan_id', $tamu->kategori_kunjungan_id)
            ->with('user')
            ->find($tamu->penanggung_jawab_id);


        if ($pic) {
            $tamu->update(['pic_id' => $pic->id]);
            $sent = $this->whatsappService->sendNotificationToPIC($pic, $tamu);
            if (!$sent) {
                throw new \Exception("Failed to send WhatsApp notification to PIC");
            }
            Log::info("Notification sent to PIC: {$pic->user->nama_lengkap} for Tamu ID: {$tamu->id}");
        } else {
            Log::warning("No active PIC found for kategori_kunjungan_id: {$tamu->kategori_kunjungan_id}");
        }
    }

    public function failed(TamuRegistered $event, $exception)
    {
        Log::error('Failed to send notification to PIC after 3 attempts: ' . $exception->getMessage(), [
            'tamu_id' => $event->tamu->id,
            'exception' => $exception
        ]);
    }
}
