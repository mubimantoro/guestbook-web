<?php

namespace App\Events;

use App\Models\Tamu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TamuRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tamu;

    /**
     * Create a new event instance.
     */
    public function __construct(Tamu $tamu)
    {
        $this->tamu = $tamu;
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
