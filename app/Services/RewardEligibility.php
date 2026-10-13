<?php

namespace App\Services;

use App\Models\StationUser;
use App\Models\User;

class RewardEligibility
{
    public const FINAL_STATION_ID = 4;
    public const REQUIRED_STATION_IDS = [1, 2, 3];

    public function forUser(User $user): array
    {
        $stationIds = StationUser::where('user_id', $user->id)
            ->pluck('station_id')->map(fn ($id) => (int) $id)->all();
        $completed = array_values(array_intersect(self::REQUIRED_STATION_IDS, $stationIds));
        $missing = array_values(array_diff(self::REQUIRED_STATION_IDS, $stationIds));
        $redeemed = in_array(self::FINAL_STATION_ID, $stationIds, true);

        return [
            'user_id' => $user->id,
            'station_id' => self::FINAL_STATION_ID,
            'eligible' => empty($missing) && !$redeemed,
            'redeemed' => $redeemed,
            'required_station_ids' => self::REQUIRED_STATION_IDS,
            'completed_station_ids' => $completed,
            'missing_station_ids' => $missing,
        ];
    }
}
