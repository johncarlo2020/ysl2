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
      "name" => "DISCOVER THE PERFECT YSL LOVENUDE COMBO",
      "description" =>
        "Discover the new YSL LOVENUDE LIP STAIN & YSL LOVENUDE KISS SHAPER, the two-step nude ritual for undressed sensual lips.",
    ]);

    Station::firstOrCreate([
      "name" => "STRIKE A POSE",
      "description" =>
        "Strike a pose, share your LOVENUDE look on social media & hashtag #YSLBeautyMY",
    ]);

    Station::firstOrCreate([
      "name" => "A LOVE LETTER
TO YOURSELF ​",
      "description" =>
        "Leave a love note to the one person who deserves it most – you.",
    ]);

    Station::firstOrCreate([
      "name" => "REDEEM YOUR GIFT",
      "description" =>
        "Redeem your YSL Discovery Gift at the gift redemption counter.",
    ]);

    Role::findOrCreate("client", "web");
  }
}
