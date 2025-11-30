<?php

namespace App\Listeners;

use App\Events\StatusTamuUpdated;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendStatusUpdateToTamu implements ShouldQueue
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
    public function handle(StatusTamuUpdated $event): void
    {
        $tamu = $event->tamu;

        $tamu->load(['kategoriKunjungan', 'pic.user']);

        if ($event->isMeet) {
            $sent = $this->whatsappService->sendFeedbackNotification($tamu);
            if ($sent) {
                Log::info("Feedback notification sent to Tamu ID: {$tamu->id}");
            } else {
                throw new \Exception("Failed to sendFeddbackNotification");
            }
        } else {
            $sent = $this->whatsappService->sendNotMeetNotification($tamu, $event->notMeetReason);

            if ($sent) {
                Log::info("Not Meet notification sent to Tamu ID: {$tamu->id}");
            } else {
                throw new \Exception("Failed to sendNotMeetNotification");
            }
        }
    }
}
