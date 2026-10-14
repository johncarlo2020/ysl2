<?php

namespace App\Services;

use App\Models\User;
use App\Events\NfcAssignmentChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RfidAssignment
{
    public function unassign(User $user): void
    {
        $user->rfid_uid = null;
        $user->save();
        $this->notify($user);
    }

    public function assign(User $user, mixed $uid): void
    {
        DB::transaction(function () use ($user, $uid) {
            // Use the same attendee lock as station redemption to prevent relinking races.
            $attendee = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($attendee->stationUser()->where('station_id', RewardEligibility::FINAL_STATION_ID)->exists()) {
                throw ValidationException::withMessages([
                    'rfid_uid' => 'This attendee has already redeemed their reward. Card linking is closed.',
                ]);
            }

            $data = Validator::make(['rfid_uid' => is_string($uid) ? trim($uid) : $uid], [
                'rfid_uid' => ['required', 'string', 'max:64', Rule::unique('users', 'rfid_uid')->ignore($attendee->id)],
            ])->validate();

            $attendee->rfid_uid = $data['rfid_uid'];
            $attendee->save();
            $this->notify($attendee);
        });
        $user->refresh();
    }

    private function notify(User $user): void
    {
        $event = new NfcAssignmentChanged($user->id, !empty($user->rfid_uid));
        DB::afterCommit(function () use ($event) {
            try {
                NfcAssignmentChanged::dispatch($event->userId, $event->hasNfc);
            } catch (\Throwable $exception) {
                // A Pusher outage must not undo a successful card assignment.
                report($exception);
            }
        });
    }
}
