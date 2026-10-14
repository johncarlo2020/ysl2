<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NfcAssignmentChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $userId, public bool $hasNfc)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'nfc.assignment.changed';
    }

    public function broadcastWith(): array
    {
        return ['has_nfc' => $this->hasNfc];
    }
}
