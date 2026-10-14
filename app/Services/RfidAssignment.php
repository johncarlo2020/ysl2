<?php

namespace App\Services;

use App\Models\User;
use App\Events\NfcAssignmentChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $data = Validator::make(['rfid_uid' => is_string($uid) ? trim($uid) : $uid], [
            'rfid_uid' => ['required', 'string', 'max:64', Rule::unique('users', 'rfid_uid')->ignore($user->id)],
        ])->validate();

        $user->rfid_uid = $data['rfid_uid'];
        $user->save();
        $this->notify($user);
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
