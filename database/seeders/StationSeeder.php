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
            'name' => "MEET THE NEW ICONIC",
            'description' => 'Discover YSL LOVENUDE Lip Blusher in more pigment with sheer-buildable, second-skin nude lip fits designed to match your undertone.',
        ]);

        Station::create([
            'name' => 'THE SHADE ROOM',
            'description' => 'Strike a pose, share your LOVENUDE look on social media & hashtag #YSLBeautyMY.',
        ]);

        Station::create([
            'name' => "LOVENUDE HOTLINE",
            'description' => "Pick up the phone. Listen to Dua Lipas's audio record and learn more about YSL LOVENUDE.",
        ]);

        Station::create([
            'name' => "BREAK THE GLASS",
            'description' => "Redeem your complimentary Nude Obsession discovery kit at the gift redemption counter.",
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
