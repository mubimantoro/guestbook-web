<?php

namespace App\Events;

use App\Models\RiwayatRescheduleKunjungan;
use App\Models\Tamu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TamuRescheduled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tamu;
    public $reschedule;
    public $sendWhatsapp;

    /**
     * Create a new event instance.
     */
    public function __construct(Tamu $tamu, RiwayatRescheduleKunjungan $reschedule, bool $sendWhatsApp = true)
    {
        $this->tamu = $tamu;
        $this->reschedule = $reschedule;
        $this->sendWhatsapp = $sendWhatsApp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
