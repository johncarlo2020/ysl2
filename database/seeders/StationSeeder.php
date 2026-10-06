<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use Spatie\Permission\Models\Role;


class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Station::firstOrCreate([
            'name' => "MEET THE NEW ICONIC",
            'description' => 'Discover YSL LOVENUDE Lip Blusher in more pigment with sheer-buildable, second-skin nude lip fits designed to match your undertone.',
        ]);

        Station::firstOrCreate([
            'name' => 'THE SHADE ROOM',
            'description' => 'Strike a pose, share your LOVENUDE look on social media & hashtag #YSLBeautyMY.',
        ]);

        Station::firstOrCreate([
            'name' => "LOVENUDE HOTLINE",
            'description' => "Pick up the phone. Listen to Dua Lipas's audio record and learn more about YSL LOVENUDE.",
        ]);

        Station::firstOrCreate([
            'name' => "BREAK THE GLASS",
            'description' => "Redeem your complimentary Nude Obsession discovery kit at the gift redemption counter.",
        ]);

        Role::findOrCreate('client', 'web');

    }
}
