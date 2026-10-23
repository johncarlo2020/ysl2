<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AttendeeRegistered implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $userId)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('nfc-registrations')];
    }

    public function broadcastAs(): string
    {
        return 'attendee.registered';
    }

    public function broadcastWith(): array
    {
        // Clients reload their authorized page; never broadcast attendee details.
        return ['user_id' => $this->userId];
    }
}
