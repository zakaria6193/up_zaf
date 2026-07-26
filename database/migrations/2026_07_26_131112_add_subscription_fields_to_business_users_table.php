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
        Schema::table('business_users', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('password');
            $table->timestamp('trial_ends_at')->nullable()->after('is_premium');
            $table->string('google_id')->nullable()->unique()->after('trial_ends_at');
            $table->string('avatar')->nullable()->after('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'trial_ends_at', 'google_id', 'avatar']);
        });
    }
};
