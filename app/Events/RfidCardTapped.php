<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RfidCardTapped implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;

    public function __construct(string $uid)
    {
        $this->uid = strtoupper(trim($uid));
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rfid'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'card.tapped';
    }
}
