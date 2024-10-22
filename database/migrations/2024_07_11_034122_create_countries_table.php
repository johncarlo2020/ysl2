<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique(); // Assuming ISO 3166-1 alpha-2 country codes
            $table->string('phone_code', 10)->nullable();
            $table->timestamps();
        });

        // Optionally, you can seed the table with initial data here
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
