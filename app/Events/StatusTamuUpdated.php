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

class StatusTamuUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tamu;
    public $isMeet;
    public $notMeetReason;

    /**
     * Create a new event instance.
     */
    public function __construct(Tamu $tamu, bool $isMeet, $notMeetReason = null)
    {
        $this->tamu = $tamu;
        $this->isMeet = $isMeet;
        $this->notMeetReason = $notMeetReason;
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
