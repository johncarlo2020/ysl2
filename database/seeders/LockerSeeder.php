<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locker;
use App\Models\RefillLog;
use App\Models\User;
use Carbon\Carbon;

class LockerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lockers = [
            [
                'name' => '1',
                'sku_code' => 'LG028101',
                'description' => 'YSL MYSLF PHONE RING S2 25 FCA',
                'tier' => 'medium',
                'percentage' => 5.64,
                'allocation' => 100,
                'available' => 100,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:40:53'),
            ],
            [
                'name' => '2',
                'sku_code' => 'LG029601',
                'description' => 'YSL XMAS 25 MIRROR GWP FCA',
                'tier' => 'medium',
                'percentage' => 11.28,
                'allocation' => 150,
                'available' => 150,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-12-09 08:46:13'),
            ],
            [
                'name' => '3',
                'sku_code' => 'LG027801',
                'description' => 'YSL LIBRE KEYCHAIN S2 25 FCA',
                'tier' => 'medium',
                'percentage' => 7.52,
                'allocation' => 100,
                'available' => 100,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:50:44'),
            ],
            [
                'name' => '4',
                'sku_code' => '',
                'description' => 'LIBRE EDP 1.2ML MYSLF EDP 1.2ML IMG 1ML X2',
                'tier' => 'common',
                'percentage' => 26.32,
                'allocation' => 350,
                'available' => 350,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:29:28'),
            ],
            [
                'name' => '5',
                'sku_code' => 'L8029403, LB467406',
                'description' => 'TPS MOISTUREGLOW T5ML TS/NP RIS, MVEFC 2ML MINI NU R22 /TRUST',
                'tier' => 'common',
                'percentage' => 11.28,
                'allocation' => 200,
                'available' => 200,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:00:36'),
            ],
            [
                'name' => '6',
                'sku_code' => 'LB405401',
                'description' => 'LVDP BLOUSE MINI 7.5ML TS/MAD',
                'tier' => 'rare',
                'percentage' => 11.28,
                'allocation' => 140,
                'available' => 140,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:46:48'),
            ],
            [
                'name' => '7',
                'sku_code' => 'LC658600, LF511300',
                'description' => 'RPC 1966 MINI, AH GLOW FDT 5ML TUBE LN1',
                'tier' => 'common',
                'percentage' => 9.77,
                'allocation' => 140,
                'available' => 140,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:31:11'),
            ],
            [
                'name' => '8',
                'sku_code' => 'LF113700',
                'description' => 'YSLY LE PARFUM R25 10ML TS',
                'tier' => 'rare',
                'percentage' => 7.52,
                'allocation' => 100,
                'available' => 100,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-12-09 09:05:15'),
            ],
            [
                'name' => '9',
                'sku_code' => 'L5823406',
                'description' => 'BO EDP V10ML TS NU / NF3',
                'tier' => 'rare',
                'percentage' => 7.52,
                'allocation' => 100,
                'available' => 100,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 20:35:22'),
            ],
            [
                'name' => '10',
                'sku_code' => 'LG028801',
                'description' => 'YSL XMAS CHARM S2 25 FCA',
                'tier' => 'rare',
                'percentage' => 1.88,
                'allocation' => 50,
                'available' => 50,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:50:55'),
            ],
        ];

        foreach ($lockers as $locker) {
            Locker::create($locker);
        }

        // Seed some refill logs
        $adminUser = User::where('email', 'admin@example.com')->first();
        $userId = $adminUser ? $adminUser->id : null;

        $refillLogs = [
            [
                'locker_id' => 1,
                'previous_amount' => 0,
                'quantity_added' => 100,
                'new_amount' => 100,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-20 10:00:00'),
                'updated_at' => Carbon::parse('2024-11-20 10:00:00'),
            ],
            [
                'locker_id' => 2,
                'previous_amount' => 0,
                'quantity_added' => 150,
                'new_amount' => 150,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-21 14:30:00'),
                'updated_at' => Carbon::parse('2024-11-21 14:30:00'),
            ],
            [
                'locker_id' => 3,
                'previous_amount' => 0,
                'quantity_added' => 100,
                'new_amount' => 100,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-21 16:00:00'),
                'updated_at' => Carbon::parse('2024-11-21 16:00:00'),
            ],
            [
                'locker_id' => 4,
                'previous_amount' => 0,
                'quantity_added' => 350,
                'new_amount' => 350,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-22 09:15:00'),
                'updated_at' => Carbon::parse('2024-11-22 09:15:00'),
            ],
            [
                'locker_id' => 5,
                'previous_amount' => 0,
                'quantity_added' => 200,
                'new_amount' => 200,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-22 11:30:00'),
                'updated_at' => Carbon::parse('2024-11-22 11:30:00'),
            ],
            [
                'locker_id' => 6,
                'previous_amount' => 0,
                'quantity_added' => 140,
                'new_amount' => 140,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-23 16:45:00'),
                'updated_at' => Carbon::parse('2024-11-23 16:45:00'),
            ],
            [
                'locker_id' => 7,
                'previous_amount' => 0,
                'quantity_added' => 140,
                'new_amount' => 140,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-23 18:20:00'),
                'updated_at' => Carbon::parse('2024-11-23 18:20:00'),
            ],
            [
                'locker_id' => 8,
                'previous_amount' => 0,
                'quantity_added' => 100,
                'new_amount' => 100,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-24 11:20:00'),
                'updated_at' => Carbon::parse('2024-11-24 11:20:00'),
            ],
            [
                'locker_id' => 9,
                'previous_amount' => 0,
                'quantity_added' => 100,
                'new_amount' => 100,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-24 13:00:00'),
                'updated_at' => Carbon::parse('2024-11-24 13:00:00'),
            ],
            [
                'locker_id' => 10,
                'previous_amount' => 0,
                'quantity_added' => 50,
                'new_amount' => 50,
                'user_id' => $userId,
                'created_at' => Carbon::parse('2024-11-24 15:30:00'),
                'updated_at' => Carbon::parse('2024-11-24 15:30:00'),
            ],
        ];

        foreach ($refillLogs as $log) {
            RefillLog::create($log);
        }
    }
}
