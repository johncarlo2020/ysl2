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
            'name' => 'YSL WINTER FANTASY',
            'description' => 'Strike a pose at the YSL Beauty Holiday pop-up at Raffles City External Quartzite, share your photo on your socials using #YSLBeautySG #YSLHoliday',
        ]);

        Station::create([
            'name' => 'FRAGRANCE DISCOVERY',
            'description' => 'Experience the LIBRE & MYSLF fragrances at Raffles City Artsquare, near the City Hall MRT entrance',
        ]);

        Station::create([
            'name' => 'GIFT REDEMPTION',
            'description' => 'Redeem your YSL Beauty Discovery Gift at the YSL Beauty Raffles City boutique (Level 1, #01-42)',
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
            'percentage' => '17 ',
            'allocation' => '80',
            'available' => '80',
        ]);

        Locker::create([
            'name' => '2',
            'percentage' => '9.6',
            'allocation' => '100',
            'available' => '100',
        ]);

        Locker::create([
            'name' => '3',
            'percentage' => '1',
            'allocation' => '30',
            'available' => '30',
        ]);

        Locker::create([
            'name' => '4',
            'percentage' => '17',
            'allocation' => '80',
            'available' => '80',
        ]);

        Locker::create([
            'name' => '5',
            'percentage' => '9.6',
            'allocation' => '40',
            'available' => '40',
        ]);

        Locker::create([
            'name' => '6',
            'percentage' => '1',
            'allocation' => '30',
            'available' => '30',
        ]);

        Locker::create([
            'name' => '7',
            'percentage' => '17',
            'allocation' => '60',
            'available' => '60',
        ]);

        Locker::create([
            'name' => '8',
            'percentage' => '17',
            'allocation' => '140',
            'available' => '140',
        ]);

        Locker::create([
            'name' => '9',
            'percentage' => '9.8',
            'allocation' => '20',
            'available' => '20',
        ]);

        Locker::create([
            'name' => '10',
            'percentage' => '1',
            'allocation' => '30',
            'available' => '30',
        ]);

        $user->assignRole('admin');
    }
}
