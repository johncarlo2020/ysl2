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
            'name' => "HOLIDAY COLLECTOR DISCOVERY",
            'description' => 'Unwrap YSL Beauty’s most iconic creations, now dressed in brushed gold.',
        ]);

        Station::create([
            'name' => 'FRAGRANCE DISCOVERY',
            'description' => 'Entice your senses with iconic fragrances from YSL Beauty.',
        ]);

        Station::create([
            'name' => "CAPTURE YOUR GOLDEN MOMENT",
            'description' => "Snap a photo, post on social media & hashtag #YSLBeautyMY",
        ]);

        Station::create([
            'name' => "GIFT REDEMPTION ",
            'description' => "Redeem your YSL Discovery Kit at the gift redemption counter.",
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
