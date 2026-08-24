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
        Schema::create('rfid_logs', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_uid', 64)->index();
            $table->string('action', 50)->index(); // 'tap', 'assign', 'unlink', 'check'
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('status', 50); // 'success', 'error', 'duplicate', 'not_found'
            $table->text('details')->nullable(); // JSON data for additional context
            $table->timestamps();

            // Indexes for performance
            $table->index('created_at');
            $table->index(['rfid_uid', 'created_at']);
            $table->index(['action', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_logs');
    }
};
