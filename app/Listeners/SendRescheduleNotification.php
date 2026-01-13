<?php

namespace App\Listeners;

use App\Events\TamuRescheduled;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendRescheduleNotification implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(TamuRescheduled $event): void
    {
        if (!$event->sendWhatsapp) {
            return;
        }

        $sent = $this->whatsappService->sendRescheduleNotification(
            $event->tamu,
            $event->reschedule
        );

        $event->reschedule->update([
            'whatsapp_sent' => $sent
        ]);
    }

    public function failed(TamuRescheduled $event, $exception)
    {
        Log::error('Failed to send reschedule notification', [
            'tamu_id' => $event->tamu->id,
            'error' => $exception->getMessage()
        ]);
    }
}
