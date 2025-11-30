<?php

namespace App\Listeners;

use App\Events\TamuRegistered;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendNotificationToAdmin implements ShouldQueue
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
        $tamu->load('kategoriKunjungan');

        $sent = $this->whatsappService->sendNotificationToAdmin($tamu);
        if ($sent) {
            Log::info("Notification sent to Admin for Tamu ID: {$tamu->id}");
        } else {
            Log::error("Failed to send notification to Admin for Tamu ID: {$tamu->id}");
            throw new \Exception("Failed to send WhatsApp notification to Admin");
        }
    }

    public function failed(TamuRegistered $event, $exception)
    {
        Log::error('Failed to send notification to Admin after 3 attempts: ' . $exception->getMessage(), [
            'tamu_id' => $event->tamu->id,
            'exception' => $exception
        ]);
    }
}
