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
            'name' => "DISCOVER THE ICONS",
            'description' => 'Entice your senses with iconic fragrances from YSL Beauty. Get a stroll through the olfactory notes and uncover the daring ingredients.',
        ]);

        Station::create([
            'name' => 'THE REFILL ERA',
            'description' => 'Feel and explore a world of forever freedom <br> with the iconic refills.',
        ]);

        Station::create([
            'name' => "MY LIBRE MOMENT",
            'description' => "Freedom to be yourself. Freedom of choice. No compromise. Your own rules. <br> <br> Snap a photo, post on social media & hashtag <br> #YSLBEAUTYMY #YSLFRAGRANCE #YSLLIBRE",
        ]);

        Station::create([
            'name' => "FEEL-GOOD KIT <br> REDEMPTION",
            'description' => "Redeem your YSL Beauty Discovery Gift at the gift redemption counter.",
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
