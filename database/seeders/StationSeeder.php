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
            'name' => "LIBRE L'EAU NUE SUMMER FREEDOM IN A SCENT",
            'description' => 'SMELL IT. FEEL IT. <br> A new sensorial fragrance gesture for pulse points, body, and <br> hair, to suit your mood..',
        ]);

        Station::create([
            'name' => 'IMMERSE IN GOLDEN HOUR ',
            'description' => 'Step into the magical twilight hours,<br> Just before the sun sets and the night begins. <br><br> Capture the unforgettable moment <br> and share with your loved ones.',
        ]);

        Station::create([
            'name' => "THE LIBRE L'EAU NU[D]E LOOK ",
            'description' => "Discover YSL Beauty's sun-kissed look that radiates Summer <br> Freedom, just like Dua Lipa.",
        ]);

        Station::create([
            'name' => "GIFT REDEMPTION ",
            'description' => "You've completed the <br> 'Summer Freedom' experience. <br> <br>Redeem your YSL Discovery Kit at the gift redemption <br> counter .",
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
