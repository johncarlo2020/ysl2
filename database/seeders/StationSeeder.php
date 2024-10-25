<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\Regime;
use Spatie\Permission\Models\Role;
use App\Models\User;
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
            'description' => 'Strike a pose at the YSL Beauty Holiday pop-up at Raffles City External Quartzite, share your photo on social media & hashtag #YSLBeautySG',
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

        $user->assignRole('admin');
    }
}
