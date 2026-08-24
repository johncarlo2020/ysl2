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
        Schema::table('users', function (Blueprint $table) {
            // Add index to rfid_uid for faster lookups (if not already indexed)
            if (!Schema::hasColumn('users', 'rfid_uid')) {
                $table->string('rfid_uid', 64)->nullable()->unique()->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Index removal is handled by the original migration
    }
};
