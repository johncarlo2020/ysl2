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
            'name' => "YSL LOVESHINE GLOSS DISCOVERY",
            'description' => 'Discover the ingredients behind the <br> YSL Loveshine Plumping Lip Oil Gloss collection.',
        ]);

        Station::create([
            'name' => 'THE LOVE GAME CHALLENGE',
            'description' => 'Get ready to participate in the love games <br> with our game ambassadors.',
        ]);

        Station::create([
            'name' => "THE LOVESHINE STUDIO",
            'description' => "Experience the YSL Loveshine photobooth,  <br> Post on social media & hashtag #YSLBEAUTYSG <br> #YSLBEAUTYLOVEGAME #YSLMAKEUP",
        ]);

        Station::create([
            'name' => "GIFT REDEMPTION ",
            'description' => "Redeem your YSL Beauty Discovery Gift at the <br> gift redemption counter.",
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
