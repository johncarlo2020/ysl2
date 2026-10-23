<?php

namespace App\Listeners;

use App\Events\AttendeeRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class BroadcastAttendeeRegistration
{
    public function handle(Registered $event): void
    {
        if ($event->user->hasAnyRole(['admin', 'staff'])) {
            return;
        }
        $userId = (int) $event->user->getKey();
        DB::afterCommit(function () use ($userId) {
            try {
                AttendeeRegistered::dispatch($userId);
            } catch (\Throwable $exception) {
                // A live-update outage must not reject a successful registration.
                report($exception);
            }
        });
    }
}
