<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('businesses')
            ->where('qr_style', 'frame')
            ->update(['qr_style' => 'pulse']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible: Frame was replaced by Emblem (logo-in-QR).
    }
};
