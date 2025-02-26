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
            'name' => 'FRAGRANCE DISCOVERY',
            'description' => 'Revel in lasting scents from the most iconic collections.',
        ]);

        Station::create([
            'name' => 'MAKEUP DISCOVERY',
            'description' => 'Reveal your iconic festive look. Embrace the inner shine within you and own a radiant look with makeup favourites.',
        ]);

        Station::create([
            'name' => 'GIFT REDEMPTION',
            'description' => 'You`ve completed the Unleash Your Inner Lights experience. Seize your exclusive gift at the gift redemption counter.',
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
