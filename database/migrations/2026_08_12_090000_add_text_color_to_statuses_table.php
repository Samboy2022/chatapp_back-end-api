<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coloured text statuses send both a background and the text colour proven
 * legible against it. `background_color` already had a column; this adds the
 * matching one so the pair survives a round trip instead of the API echoing
 * back a hardcoded null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('text_color', 16)->nullable()->after('background_color');
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('text_color');
        });
    }
};
