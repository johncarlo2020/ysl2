<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RfidAssignment
{
    public function unassign(User $user): void
    {
        $user->rfid_uid = null;
        $user->save();
    }

    public function assign(User $user, mixed $uid): void
    {
        $data = Validator::make(['rfid_uid' => is_string($uid) ? trim($uid) : $uid], [
            'rfid_uid' => ['required', 'string', 'max:64', Rule::unique('users', 'rfid_uid')->ignore($user->id)],
        ])->validate();

        $user->rfid_uid = $data['rfid_uid'];
        $user->save();
    }
}
