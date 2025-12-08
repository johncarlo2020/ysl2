<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refill_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('locker_id');
            $table->integer('previous_amount');
            $table->integer('quantity_added');
            $table->integer('new_amount');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('locker_id')->references('id')->on('lockers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refill_logs');
    }
};
