<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locker;
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
                'percentage' => 5.64, // 150/2660 * 100
                'allocation' => 150,
                'available' => 150,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:40:53'),
            ],
            [
                'name' => '2',
                'sku_code' => 'LG029601',
                'description' => 'YSL XMAS 25 MIRROR GWP FCA',
                'percentage' => 11.28, // 300/2660 * 100
                'allocation' => 300,
                'available' => 300,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-12-09 08:46:13'),
            ],
            [
                'name' => '3',
                'sku_code' => 'LG027801',
                'description' => 'YSL LIBRE KEYCHAIN S2 25 FCA',
                'percentage' => 7.52, // 200/2660 * 100
                'allocation' => 200,
                'available' => 200,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:50:44'),
            ],
            [
                'name' => '4',
                'sku_code' => '',
                'description' => 'LIBRE EDP 1.2ML MYSLF EDP 1.2ML IMG 1ML X2',
                'percentage' => 26.32, // 700/2660 * 100
                'allocation' => 700,
                'available' => 700,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:29:28'),
            ],
            [
                'name' => '5',
                'sku_code' => 'L8029403, LB467406',
                'description' => 'TPS MOISTUREGLOW T5ML TS/NP RIS, MVEFC 2ML MINI NU R22 /TRUST',
                'percentage' => 11.28, // 300/2660 * 100
                'allocation' => 300,
                'available' => 300,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:00:36'),
            ],
            [
                'name' => '6',
                'sku_code' => 'LB405401',
                'description' => 'LVDP BLOUSE MINI 7.5ML TS/MAD',
                'percentage' => 11.28, // 300/2660 * 100
                'allocation' => 300,
                'available' => 300,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:46:48'),
            ],
            [
                'name' => '7',
                'sku_code' => 'LC658600, LF511300',
                'description' => 'RPC 1966 MINI, AH GLOW FDT 5ML TUBE LN1',
                'percentage' => 9.77, // 260/2660 * 100
                'allocation' => 260,
                'available' => 260,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 21:31:11'),
            ],
            [
                'name' => '8',
                'sku_code' => 'LF113700',
                'description' => 'YSLY LE PARFUM R25 10ML TS',
                'percentage' => 7.52, // 200/2660 * 100
                'allocation' => 200,
                'available' => 200,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-12-09 09:05:15'),
            ],
            [
                'name' => '9',
                'sku_code' => 'L5823406',
                'description' => 'BO EDP V10ML TS NU / NF3',
                'percentage' => 7.52, // 200/2660 * 100
                'allocation' => 200,
                'available' => 200,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 20:35:22'),
            ],
            [
                'name' => '10',
                'sku_code' => 'LG028801',
                'description' => 'YSL XMAS CHARM S2 25 FCA',
                'percentage' => 1.88, // 50/2660 * 100
                'allocation' => 50,
                'available' => 50,
                'created_at' => Carbon::parse('2024-11-13 03:27:21'),
                'updated_at' => Carbon::parse('2024-11-24 19:50:55'),
            ],
        ];

        foreach ($lockers as $locker) {
            Locker::create($locker);
        }
    }
}
