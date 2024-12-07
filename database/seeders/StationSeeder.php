<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\Regime;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Locker;

use Illuminate\Support\Facades\Hash;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Station::create([
            'name' => 'YSL HOLIDAY DISCOVERY',
            'description' => 'Dare to discover the irresistible YSL Beauty Holiday collectors and advent calendar',
        ]);

        Station::create([
            'name' => 'THE LIBRE EXPERIENCE',
            'description' => 'Discover the LIBRE immersive fragrance room and share your photo/video on your socials using #YSLBeautyMY #YSLHoliday',
        ]);

        Station::create([
            'name' => 'GIFT REDEMPTION',
            'description' => 'Redeem your YSL Beauty Discovery Gift at the gift redemption counter',
        ]);

        $role = Role::create(['name' => 'client']);

        $role = Role::create(['name' => 'admin']);

        $user = User::create([
            'code' => '0000000000000',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('WowsomeYsl'),
        ]);

        Locker::create([
            'name' => '1',
            'percentage' => '13',
            'allocation' => '80',
            'available' => '80',
        ]);

        Locker::create([
            'name' => '2',
            'percentage' => '20',
            'allocation' => '100',
            'available' => '100',
        ]);

        Locker::create([
            'name' => '3',
            'percentage' => '5',
            'allocation' => '30',
            'available' => '30',
        ]);

        Locker::create([
            'name' => '4',
            'percentage' => '13',
            'allocation' => '80',
            'available' => '80',
        ]);

        Locker::create([
            'name' => '5',
            'percentage' => '4',
            'allocation' => '40',
            'available' => '40',
        ]);

        Locker::create([
            'name' => '6',
            'percentage' => '5',
            'allocation' => '30',
            'available' => '30',
        ]);

        Locker::create([
            'name' => '7',
            'percentage' => '10',
            'allocation' => '60',
            'available' => '60',
        ]);

        Locker::create([
            'name' => '8',
            'percentage' => '24',
            'allocation' => '140',
            'available' => '140',
        ]);

        Locker::create([
            'name' => '9',
            'percentage' => '2',
            'allocation' => '20',
            'available' => '20',
        ]);

        Locker::create([
            'name' => '10',
            'percentage' => '5',
            'allocation' => '30',
            'available' => '30',
        ]);

        $user->assignRole('admin');
    }
}
