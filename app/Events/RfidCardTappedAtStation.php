<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RfidCardTappedAtStation implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public int $stationId;

    public function __construct(string $uid, int $stationId)
    {
        $this->uid = strtoupper(trim($uid));
        $this->stationId = $stationId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rfid-station-' . $this->stationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'card.tapped';
    }

    public function broadcastWith(): array
    {
        return [
            'uid' => $this->uid,
            'station_id' => $this->stationId,
        ];
    }
}
