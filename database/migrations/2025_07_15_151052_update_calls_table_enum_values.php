<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the call_type enum to include 'voice' as well as 'audio' and 'video'
        DB::statement("ALTER TABLE calls MODIFY COLUMN call_type ENUM('audio', 'video', 'voice')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE calls MODIFY COLUMN call_type ENUM('audio', 'video')");
    }
};
